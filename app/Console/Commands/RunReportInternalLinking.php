<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunReportInternalLinking extends Command
{
    protected $signature = 'report:internal-linking {--limit=0 : Max number of report descriptions to process (0 = all)}';
    protected $description = 'Cross-link report descriptions: link every other report\'s keyword found in a description (max 5 links per description, one per paragraph, no duplicate targets)';

    public function handle()
    {
        $languages = DB::table('languages')->get()->keyBy('id');

        $rows = DB::table('reports_info as ri')
            ->join('reports as r', 'r.report_id', '=', 'ri.report_id')
            ->join('report_descriptions as rd', 'rd.info_id', '=', 'ri.id')
            ->where('ri.is_deleted', 0)
            ->where('ri.is_publish', 1)
            ->whereNotNull('rd.description')
            ->select('ri.id as info_id', 'ri.report_id', 'ri.language_id', 'ri.keyword', 'r.report_url', 'rd.description')
            ->get();

        $limit = (int) $this->option('limit');
        $processed = 0;
        $updated = 0;

        foreach ($rows->groupBy('language_id') as $languageId => $group) {
            $languageCode = $languages[$languageId]->code ?? 'en';

            // Every report's keyword + URL in this language — the shared candidate pool
            $pool = $group->map(fn($row) => [
                'report_id' => $row->report_id,
                'keyword' => $row->keyword,
                'url' => url("/{$languageCode}/report/{$row->report_url}"),
            ]);

            foreach ($group as $row) {
                if ($limit && $processed >= $limit) {
                    break 2;
                }
                $processed++;

                if (empty($row->keyword) || empty($row->description)) {
                    continue;
                }

                $candidates = $pool->reject(fn($c) => $c['report_id'] === $row->report_id)->values()->all();

                [$newHtml, $addedCount] = apply_internal_linking_to_description($row->description, $candidates, 5);

                if ($addedCount > 0) {
                    DB::table('report_descriptions')->where('info_id', $row->info_id)->update([
                        'description' => $newHtml,
                    ]);
                    $updated++;
                    $this->info("Report #{$row->report_id} ({$languageCode}): +{$addedCount} internal link(s)");
                }
            }
        }

        $this->info("Done. Processed {$processed} description(s), updated {$updated}.");

        return self::SUCCESS;
    }
}
