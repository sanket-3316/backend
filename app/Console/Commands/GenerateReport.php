<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportKeyword;
use App\Jobs\GenerateReportJob;

class GenerateReport extends Command
{
    protected $signature = 'report:generate';
    protected $description = 'Generate report from keyword (one by one)';

    public function handle()
    {
        // Get only ONE pending keyword
        $keyword = ReportKeyword::where('report_status', 'pending')
            ->orderBy('id', 'asc')
            ->first();

        if (!$keyword) {
            $this->info('No pending keywords');
            return;
        }

        // Mark as processing (important to avoid duplicate pick)
        $keyword->update([
            'report_status' => 'processing'
        ]);

        // Dispatch job
        GenerateReportJob::dispatch($keyword);

        $this->info("Processing: {$keyword->keyword}");
    }
}
