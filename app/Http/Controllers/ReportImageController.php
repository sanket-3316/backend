<?php

namespace App\Http\Controllers;

use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportImageController extends Controller
{
    // GET /report/{slug}/{filename} — e.g. /report/generative-ai-market/market_overview.webp
    // or /report/generative-ai-market/by-component.webp
    public function render(Request $request, $slug, $filename)
    {
        $localeHeader = $request->header('Accept-Language');
        $locale = $localeHeader ? substr($localeHeader, 0, 2) : 'en';

        $language = Languages::where('code', $locale)->first();
        if (!$language) {
            $language = Languages::where('is_default', 1)->first();
        }

        $report = DB::table('reports as r')
            ->join('reports_info as ri', function ($join) use ($language) {
                $join->on('r.report_id', '=', 'ri.report_id')
                    ->where('ri.language_id', $language->id)
                    ->where('ri.is_deleted', 0)
                    ->where('ri.is_publish', 1);
            })
            ->leftJoin('report_descriptions as rd', 'ri.id', '=', 'rd.info_id')
            ->where('r.report_url', $slug)
            ->select(
                'r.historic_year',
                'r.forecast_year',
                'r.base_year_market_size',
                'r.forecast_year_market_size',
                'ri.keyword',
                'rd.segmentation'
            )
            ->first();

        if (!$report) {
            abort(404);
        }

        $imageKey = pathinfo($filename, PATHINFO_FILENAME);

        $svg = $imageKey === 'market_overview'
            ? $this->buildMarketOverviewSvg($report)
            : $this->buildSegmentSvg($report, $imageKey);

        if (!$svg) {
            abort(404);
        }

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    // GET /report/thumbnail/{filename} — e.g. /report/thumbnail/biogas-market.svg?lang=ja
    // <img> tags can't set Accept-Language, so the language comes from ?lang=
    // instead (falling back to English for anything unsupported).
    public function renderCardThumbnail(Request $request, $filename)
    {
        $slug = pathinfo($filename, PATHINFO_FILENAME);

        $report = DB::table('reports as r')
            ->join('reports_info as ri', function ($join) {
                $join->on('r.report_id', '=', 'ri.report_id')
                    ->where('ri.is_deleted', 0)
                    ->where('ri.is_publish', 1);
            })
            ->where('r.report_url', $slug)
            ->select('ri.keyword')
            ->first();

        if (!$report) {
            abort(404);
        }

        $lang = strtolower((string) $request->query('lang', 'en'));

        $svg = $this->buildCardThumbnailSvg($report, $lang);

        if (!$svg) {
            abort(404);
        }

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    private function buildCardThumbnailSvg($report, $lang)
    {
        $path = public_path('uploads/reports/thumbnail/business-card.svg');
        if (!file_exists($path)) {
            return null;
        }

        $svg = file_get_contents($path);

        $marketWord = match ($lang) {
            'ja' => '市場',
            'ko' => '시장',
            default => 'Market',
        };

        // English reads "Keyword Market"; Japanese/Korean append the word
        // directly with no separating space.
        $separator = in_array($lang, ['ja', 'ko'], true) ? '' : ' ';
        $title = trim($report->keyword) . $separator . $marketWord;

        $fontFamily = "'Open Sans','Noto Sans','Segoe UI',Roboto,Arial,Helvetica,sans-serif";
        $maxWidth = 460;

        // Scales down smoothly as the title gets longer, so it keeps fitting
        // on one line (and stays clear of the artwork/contact block) instead
        // of overflowing — this is what keeps the thumbnail responsive.
        $fontSize = (int) floor($maxWidth / (max(1, mb_strlen($title)) * 0.62));
        $fontSize = max(16, min(40, $fontSize));

        $titleBlock = build_wrapped_left_text($title, 31, 81, $fontFamily, 700, $fontSize, $maxWidth, 2);

        return str_replace('[[title_block]]', $titleBlock, $svg);
    }

    private function buildMarketOverviewSvg($report)
    {
        $path = public_path('uploads/reports/thumbnail/slide1.SVG');
        if (!file_exists($path)) {
            return null;
        }

        $svg = file_get_contents($path);

        $historicYear = (int) ($report->historic_year ?: (date('Y') - 7));
        $forecastYear = (int) ($report->forecast_year ?: (date('Y') + 9));

        $years = range($historicYear, $forecastYear);
        $count = count($years);

        $baseYearSize = $this->formatNumber($report->base_year_market_size);
        $heading = "Global {$report->keyword} Market Size, USD {$baseYearSize}";

        $replacements = [
            '[[heading_block]]' => build_wrapped_center_text($heading, 622, 114, 'Roboto,Roboto_MSFontService,sans-serif', 700, 24, 860),
            '[[base_year_market_size]]' => $baseYearSize,
            '[[forecast_year_market_size]]' => $this->formatNumber($report->forecast_year_market_size),
        ];

        // The template has exactly 16 year labels — sample 16 evenly spaced years
        // from the report's own historic-to-forecast range, however wide that is.
        for ($i = 0; $i < 16; $i++) {
            $idx = $count > 1 ? (int) round($i * ($count - 1) / 15) : 0;
            $replacements['[[year' . ($i + 1) . ']]'] = $years[$idx] ?? $years[$count - 1];
        }

        return str_replace(array_keys($replacements), array_values($replacements), $svg);
    }

    private function buildSegmentSvg($report, $imageKey)
    {
        $segments = is_string($report->segmentation)
            ? (json_decode($report->segmentation, true) ?: [])
            : (is_array($report->segmentation) ? $report->segmentation : []);

        $matchedLabel = null;
        foreach (array_keys($segments) as $key) {
            if (Str::slug($key) === $imageKey) {
                $matchedLabel = $key;
                break;
            }
        }

        if (!$matchedLabel) {
            return null;
        }

        $path = public_path('uploads/reports/thumbnail/slide2.SVG');
        if (!file_exists($path)) {
            return null;
        }

        $svg = file_get_contents($path);

        $heading = "Global {$report->keyword} Market Share (%) {$matchedLabel}";

        $replacements = [
            '[[heading_block]]' => build_wrapped_center_text($heading, 622, 114, 'Roboto,Roboto_MSFontService,sans-serif', 700, 20, 860),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $svg);
    }

    private function formatNumber($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        return is_numeric($value) ? number_format((float) $value, 2) : $value;
    }
}
