<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\ReportKeyword;
use App\Services\ReportGenerationService;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $keyword;

    public function __construct(ReportKeyword $keyword)
    {
        $this->keyword = $keyword;
    }

    public function handle(): void
    {
        try {
            $this->keyword->update([
                'report_status' => 'processing',
                'error' => null,
            ]);

            $generated = app(ReportGenerationService::class)->generate($this->keyword);

            $service = app(ReportService::class);

            // English language id — never hardcode 1, this DB's language ids
            // aren't guaranteed to line up with insertion order.
            $englishLanguageId = DB::table('languages')->where('code', 'en')->value('id')
                ?? DB::table('languages')->where('is_default', 1)->value('id')
                ?? 1;

            // If this keyword already has an English report, UPDATE it instead
            // of inserting a new one — inserting again would violate the
            // unique report_url/keyword constraints and fail loudly for no
            // reason; regenerating the same keyword should just refresh it.
            $existingReportId = DB::table('reports_info')
                ->where('keyword', $this->keyword->keyword)
                ->where('language_id', $englishLanguageId)
                ->where('is_deleted', 0)
                ->value('report_id');

            $saveResult = $service->saveReport([
                ...$generated,
                'slug' => $this->keyword->keyword . ' market',
                'language_id' => $englishLanguageId,
                ...get_report_default_prices(),
            ], $existingReportId);

            if (!$saveResult) {
                throw new \Exception("{$this->keyword->keyword} report could not be saved — check the report form fields.");
            }

            // Regenerating an existing report leaves its other-language
            // translations stale — wipe them and reset is_translated_all_lang
            // so the next report:translate cron run refreshes every language
            // against the freshly regenerated English content.
            if ($existingReportId) {
                Report::resetTranslationsForUpdate($existingReportId, $englishLanguageId);
            }

            $this->keyword->update([
                'is_report_generated' => true,
                'report_status' => 'completed',
            ]);
        } catch (\Exception $e) {
            $this->keyword->update([
                'report_status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
