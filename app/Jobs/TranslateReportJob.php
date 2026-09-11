<?php

namespace App\Jobs;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TranslateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportId;

    public function __construct($reportId)
    {
        $this->reportId = $reportId;
    }

    public function handle(): void
    {
        $englishLanguage = DB::table('languages')->where('code', 'en')->first()
            ?? DB::table('languages')->where('is_default', 1)->first();

        if (!$englishLanguage) {
            Log::error("TranslateReportJob: no English/default language configured");
            return;
        }

        // Only reports_info + report_descriptions are source material — the
        // shared report-level fields (reports, report_prices) are the same
        // for every language and are never touched here.
        $source = DB::table('reports_info as ri')
            ->leftJoin('report_descriptions as rd', 'rd.info_id', '=', 'ri.id')
            ->where('ri.report_id', $this->reportId)
            ->where('ri.language_id', $englishLanguage->id)
            ->where('ri.is_deleted', 0)
            ->select(
                'ri.report_title',
                'ri.meta_desc',
                'ri.h1_long_title',
                'ri.keyword',
                'ri.thumbnail',
                'rd.description',
                'rd.segmentation'
            )
            ->first();

        if (!$source) {
            Log::error("TranslateReportJob: no English source content found for report #{$this->reportId}");
            return;
        }

        $targetLanguages = DB::table('languages')->where('id', '!=', $englishLanguage->id)->get();

        $alreadyTranslated = DB::table('reports_info')
            ->where('report_id', $this->reportId)
            ->where('is_deleted', 0)
            ->pluck('language_id')
            ->all();

        $allSucceeded = true;

        foreach ($targetLanguages as $language) {
            if (in_array($language->id, $alreadyTranslated)) {
                continue; // already has this language — one-by-one means never re-translating a done one
            }

            try {
                $translated = $this->translate($source, $language->name);

                $saved = Report::addTranslation($this->reportId, [
                    'language_id' => $language->id,
                    'report_title' => $translated['report_title'],
                    'meta_desc' => $translated['meta_desc'],
                    'h1_long_title' => $translated['h1_long_title'],
                    'keyword' => $translated['keyword'],
                    'thumbnail' => $source->thumbnail, // filename, not translatable — shared as-is
                    'description' => $translated['description'],
                    'segmentation' => is_array($translated['segmentation'] ?? null)
                        ? json_encode($translated['segmentation'], JSON_UNESCAPED_UNICODE)
                        : ($translated['segmentation'] ?? $source->segmentation),
                ]);

                if (!$saved) {
                    $allSucceeded = false;
                    Log::error("TranslateReportJob: failed to save {$language->code} translation for report #{$this->reportId}");
                }
            } catch (\Exception $e) {
                $allSucceeded = false;
                Log::error("TranslateReportJob: {$language->code} translation failed for report #{$this->reportId}: " . $e->getMessage());
            }
        }

        // Only flip the flag once every language in the table has a translation —
        // a partial failure leaves it at 0 so the next cron run retries just the
        // languages still missing (addTranslation's duplicate guard skips the rest).
        if ($allSucceeded) {
            DB::table('reports')->where('report_id', $this->reportId)->update([
                'is_translated_all_lang' => 1,
                'updated_at' => now(),
            ]);
        }
    }

    private function translate($source, $targetLanguageName)
    {
        $prompt = get_report_translation_prompt();

        $content = [
            'report_title' => $source->report_title,
            'meta_desc' => $source->meta_desc,
            'h1_long_title' => $source->h1_long_title,
            'keyword' => $source->keyword,
            'description' => (string) $source->description,
            'segmentation' => json_decode($source->segmentation, true) ?: [],
        ];

        $systemPrompt = str_replace('[[target_language]]', $targetLanguageName, $prompt['system']);
        $userPrompt = str_replace(
            ['[[target_language]]', '[[content_json]]'],
            [$targetLanguageName, json_encode($content, JSON_UNESCAPED_UNICODE)],
            $prompt['user']
        );

        $result = search_from_gpt($userPrompt, $systemPrompt, 0.2, 12000, 'json_object');

        if (empty($result)) {
            throw new \Exception("GPT did not return a translation into {$targetLanguageName}");
        }

        $translated = json_decode($result, true);

        if (!is_array($translated) || empty($translated['report_title']) || empty($translated['description'])) {
            throw new \Exception("Invalid/incomplete translation JSON for {$targetLanguageName}");
        }

        return $translated;
    }
}
