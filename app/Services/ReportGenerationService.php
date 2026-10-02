<?php

namespace App\Services;

use App\Models\Prompt;
use Illuminate\Support\Facades\DB;

class ReportGenerationService
{
    // Builds the full report content (description + FAQs + meta + category)
    // from a report_keywords row that already carries the user-provided
    // market size/CAGR/segments/companies — ONE GPT call, driven entirely by
    // the active "report" type prompt in the prompts table (no hardcoded
    // PHP prompt, no separate segmentation/key-players/market-size calls).
    //
    // Returns a data array shaped for ReportService::saveReport(). Does NOT
    // save anything itself — the caller decides create vs update.
    public function generate($keywordRow): array
    {
        foreach (['base_year_market_size', 'forecast_year_market_size', 'forecast_cagr', 'segments', 'companies'] as $field) {
            if (empty($keywordRow->$field)) {
                throw new \Exception("Missing required \"$field\" on this keyword — edit it and fill that in before generating.");
            }
        }

        $segments = json_decode($keywordRow->segments, true);
        if (!is_array($segments) || empty($segments)) {
            throw new \Exception('Segments must be valid, non-empty JSON (e.g. {"By Type": ["A", "B"]})');
        }

        $companies = array_values(array_filter(array_map('trim', explode(',', $keywordRow->companies))));
        if (empty($companies)) {
            throw new \Exception('Key companies list is empty after parsing — check the comma-separated value.');
        }

        $promptRow = Prompt::getActivePrompt('report');
        if (!$promptRow) {
            throw new \Exception('No active report prompt configured. Add one in the Prompts dashboard.');
        }

        $years = report_years();

        $categoryId = $keywordRow->category_id ?: null;
        $categoryInstruction = '';
        $categoryJsonKey = '';

        if (empty($categoryId)) {
            $categories = DB::table('categories as c')
                ->join('category_translations as ct', function ($join) {
                    $join->on('c.id', '=', 'ct.category_id')->where('ct.language_id', 1);
                })
                ->select('c.id', 'ct.name')
                ->orderBy('c.id')
                ->get();

            $categoryList = $categories->map(fn($c) => "{$c->id}: {$c->name}")->implode("\n");
            $reportTitle = generate_report_title($keywordRow->keyword);

            $categoryInstruction = "CATEGORY SUGGESTION NEEDED: No category has been pre-assigned to this report. "
                . "Based on the report title \"{$reportTitle}\" and the market data above, choose the single most "
                . "suitable category from this list and return its numeric ID:\n{$categoryList}";

            $categoryJsonKey = ",\n  \"suggested_category_id\": <the chosen category id as a number, must be one of the IDs listed above>";
        }

        $placeholders = [
            '[[keyword]]' => $keywordRow->keyword,
            '[[base_year]]' => $years['base_year'],
            '[[forecast_year]]' => $years['forecast_end_year'],
            '[[historic_period]]' => $years['historic_period'],
            '[[forecast_period]]' => $years['forecast_period'],
            '[[base_year_market_size]]' => $keywordRow->base_year_market_size,
            '[[forecast_year_market_size]]' => $keywordRow->forecast_year_market_size,
            '[[forecast_cagr]]' => $keywordRow->forecast_cagr,
            '[[segments]]' => json_encode($segments, JSON_UNESCAPED_SLASHES),
            '[[companies]]' => implode(', ', $companies),
            '[[category_instruction]]' => $categoryInstruction,
            '[[category_json_key]]' => $categoryJsonKey,
        ];

        $systemPrompt = strtr($promptRow->system_prompt, $placeholders);
        $userPrompt = strtr($promptRow->user_prompt, $placeholders);

        $result = search_from_gpt(
            $userPrompt,
            $systemPrompt,
            (float) $promptRow->temperature,
            (int) $promptRow->max_tokens,
            $promptRow->response_format
        );

        if (empty($result)) {
            throw new \Exception('GPT did not return a report.');
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded) || empty($decoded['description'])) {
            throw new \Exception('Invalid report JSON returned by GPT (missing "description").');
        }

        if (!empty($categoryId)) {
            $resolvedCategoryId = (int) $categoryId;
        } else {
            $resolvedCategoryId = (int) ($decoded['suggested_category_id'] ?? 0);
            $validIds = DB::table('categories')->pluck('id')->all();
            if (!$resolvedCategoryId || !in_array($resolvedCategoryId, $validIds, true)) {
                // GPT skipped/mismatched the category id — fall back to the
                // first category rather than failing the whole report over it.
                $resolvedCategoryId = (int) DB::table('categories')->orderBy('id')->value('id');
            }
        }

        return [
            'report_title' => generate_report_title($keywordRow->keyword),
            'h1_long_title' => generate_report_h1_long_title($keywordRow->keyword, $segments),
            'meta_desc' => $decoded['meta_desc'] ?? generate_report_title($keywordRow->keyword),
            'description' => clean_gpt_html_response($decoded['description']),
            'primary_interview_insights' => clean_gpt_html_response($decoded['primary_interview_insights'] ?? '') ?: null,
            'segmentation_json' => $segments,
            'key_companys' => $companies,
            'base_year_market_size' => $keywordRow->base_year_market_size,
            'forecast_year_market_size' => $keywordRow->forecast_year_market_size,
            'forecast_cagr' => (float) preg_replace('/[^0-9.]/', '', (string) $keywordRow->forecast_cagr),
            'category_id' => $resolvedCategoryId,
            'base_year' => $years['base_year'],
            'historic_year' => $years['historic_start_year'],
            'forecast_year' => $years['forecast_end_year'],
            'keyword' => $keywordRow->keyword,
            'thumbnail' => $keywordRow->keyword . ' market',
        ];
    }
}
