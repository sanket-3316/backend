<?php

namespace App\Console\Commands;

use App\Jobs\TranslateReportJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateReport extends Command
{
    protected $signature = 'report:translate';
    protected $description = 'Pick one English report that is ready but not yet fully translated, and translate it into every other language (one report per run)';

    public function handle()
    {
        $englishLanguage = DB::table('languages')->where('code', 'en')->first()
            ?? DB::table('languages')->where('is_default', 1)->first();

        if (!$englishLanguage) {
            $this->error('No English/default language configured.');
            return self::FAILURE;
        }

        // "ready for translate" = the English reports_info row is published;
        // "not yet translated" = reports.is_translated_all_lang is still 0.
        $report = DB::table('reports as r')
            ->join('reports_info as ri', function ($join) use ($englishLanguage) {
                $join->on('r.report_id', '=', 'ri.report_id')
                    ->where('ri.language_id', $englishLanguage->id);
            })
            ->where('r.is_translated_all_lang', 0)
            ->where('ri.is_publish', 1)
            ->where('ri.is_deleted', 0)
            ->orderBy('r.report_id')
            ->select('r.report_id')
            ->first();

        if (!$report) {
            $this->info('No reports pending translation.');
            return self::SUCCESS;
        }

        TranslateReportJob::dispatch($report->report_id);

        $this->info("Dispatched translation for report #{$report->report_id}");
        return self::SUCCESS;
    }
}
