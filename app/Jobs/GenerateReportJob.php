<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\ReportKeyword;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            // Update status to processing
            $this->keyword->update([
                'report_status' => 'processing',
                'error' => null
            ]);

            $keyword = $this->keyword->keyword . " market";

            // Computed once, fed into the market-size prompt and used directly
            // for the report's own historic/base/forecast year fields below —
            // keeps GPT's estimate and the stored years consistent.
            $years = report_years();

            // get report segments
            $segments_prompt = get_report_segmentation_prompt();
            $segments_result = search_from_gpt(
                str_replace(["[[keyword]]"], [$keyword],  $segments_prompt['user']),
                $segments_prompt['system'],
                0.08,
                12500,
                "json_object",
            );
            if (empty($segments_result)) {
                throw now("GPT not return Segments");
            }
            $segments_result = json_decode($segments_result, true);

            if (!isset($segments_result['segments'])) {
                throw new \Exception("Invalid Segments JSON");
            }
            if (!isset($segments_result['h1_long_title'])) {
                throw new \Exception("h1 long title not generated");
            }

            // Generate  key players
            $key_players_prompt = get_report_key_players_prompt();
            $key_players_result = search_from_gpt(
                str_replace(["[[keyword]]"], [$keyword],  $key_players_prompt['user']),
                $key_players_prompt['system'],
                0.08,
                12500,
                "json_object",
            );

            if (empty($key_players_result)) {
                throw now("GPT not return key players");
            }
            $key_players_result =  json_decode($key_players_result, true);

            // Generate  market size data
            $market_size_data_prompt = get_report_market_size_data_prompt($years);
            $market_size_data_result = search_from_gpt(
                str_replace(["[[keyword]]"], [$keyword],  $market_size_data_prompt['user']),
                $market_size_data_prompt['system'],
                0.08,
                12500,
                "json_object",
            );
            if (empty($market_size_data_result)) {
                throw now("GPT Not return Market Data");
            }
            $market_size_data_result =  json_decode($market_size_data_result, true);

            // Generate report description
            $description_prompt = get_report_description_prompt(
                $this->keyword->keyword,
                $segments_result,
                [
                    'base_year' => $years['base_year'],
                    'forecast_year' => $years['forecast_end_year'],
                    'base_year_market_size' => $market_size_data_result['base_year_market_size'] ?? '',
                    'forecast_market_size' => $market_size_data_result['forecast_market_size'] ?? '',
                    'cagr_percent' => $market_size_data_result['cagr_percent'] ?? '',
                ],
                $key_players_result
            );

            $rd_result = search_from_gpt(
                $description_prompt['user'],
                $description_prompt['system'],
                0.4,
                8000,
                "text"
            );
            $rd_result = clean_gpt_html_response($rd_result);
            if (empty($rd_result)) {
                throw now("GPT Not return report description");
            }
            // $rd_result =  json_decode($rd_result, true);


            $service = app(ReportService::class);

            // English language id — never hardcode 1, this DB's language ids
            // aren't guaranteed to line up with insertion order (verified:
            // en=1 but ja/ko landed as 3/2 here because of seeding order).
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
                'report_title' => generate_report_title($this->keyword->keyword),
                'slug' => $this->keyword->keyword . " market",
                'category_id' => 1,
                'language_id' => $englishLanguageId,

                'base_year' => $years['base_year'],
                'historic_year' => $years['historic_start_year'],
                'forecast_year' => $years['forecast_end_year'],

                'base_year_market_size' => $market_size_data_result['base_year_market_size'] ?? '',
                'forecast_year_market_size' => $market_size_data_result['forecast_market_size'] ?? '',
                'forecast_cagr' => $market_size_data_result['cagr_percent'] ?? 0,

                'key_companys' => $key_players_result['key_players'] ?? [],

                // GPT occasionally omits an optional-looking key even in JSON
                // mode (this exact field has crashed a real run before with
                // "Undefined array key" — see ReportKeyword #7) — fall back to
                // the report title rather than letting the whole job die.
                'meta_desc' => $market_size_data_result['meta_description'] ?? generate_report_title($this->keyword->keyword),
                'h1_long_title' => $segments_result['h1_long_title'] ?? generate_report_h1_long_title($this->keyword->keyword, $segments_result['h1_long_title']),
                'keyword' => $this->keyword->keyword,
                'thumbnail' =>  $this->keyword->keyword . ' market',

                'description' => $rd_result,
                'segmentation_json' => $segments_result['segments'],

                ...get_report_default_prices(),
            ], $existingReportId);

            // Regenerating an existing report leaves its other-language
            // translations stale — wipe them and reset is_translated_all_lang
            // so the next report:translate cron run refreshes every language
            // against the freshly regenerated English content.
            if ($saveResult && $existingReportId) {
                Report::resetTranslationsForUpdate($existingReportId, $englishLanguageId);
            }

            $this->keyword->update([
                'is_report_generated' => true,
                'report_status' => 'completed',
            ]);
        } catch (\Exception $e) {
            $this->keyword->update([
                'report_status' => 'failed',
                'error' => $e->getMessage()
            ]);
        }
    }
}
