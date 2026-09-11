<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:generate-report')
    // ->cron('0 */1 * * *')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->sendOutputTo(storage_path('logs/generate_report_from_keyword.log'));

Schedule::command('report:internal-linking')
    ->daily()
    ->withoutOverlapping()
    ->sendOutputTo(storage_path('logs/report_internal_linking.log'));

Schedule::command('report:translate')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->sendOutputTo(storage_path('logs/report_translate.log'));
