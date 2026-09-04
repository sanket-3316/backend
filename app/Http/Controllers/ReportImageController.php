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
                'r.base_year',
                'r.forecast_year',
                'r.base_year_market_size',
                'r.forecast_year_market_size',
                'r.forecast_cagr',
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

    // Fully computed at runtime — no template file, no image library. Bars are
    // plain <rect> elements whose height is a real proportion of a value series
    // derived from the report's own base/forecast data, not a static drawing.
    private function buildMarketOverviewSvg($report)
    {
        $historicYear = (int) ($report->historic_year ?: (date('Y') - 7));
        $baseYear = (int) ($report->base_year ?: $historicYear);
        $forecastYear = (int) ($report->forecast_year ?: (date('Y') + 9));

        if ($forecastYear <= $historicYear) {
            $forecastYear = $historicYear + 1;
        }

        [$baseValue, $baseUnit] = parse_market_value($report->base_year_market_size);
        [$forecastValue, $forecastUnit] = parse_market_value($report->forecast_year_market_size);
        $unit = $forecastUnit ?: ($baseUnit ?: 'Mn');

        $years = range($historicYear, $forecastYear);
        $series = $this->buildYearlySeries($years, $baseYear, $forecastYear, $baseValue, $forecastValue);

        [$cagrValue] = parse_market_value($report->forecast_cagr);
        if ($cagrValue <= 0) {
            $span = $forecastYear - $baseYear;
            $cagrValue = ($span > 0 && $baseValue > 0)
                ? (pow($forecastValue / $baseValue, 1 / $span) - 1) * 100
                : 0.0;
        }

        return $this->renderBarChartSvg($report->keyword, $years, $series, $unit, $historicYear, $baseYear, $forecastYear, $cagrValue);
    }

    // Anchors the curve exactly at (baseYear, baseValue) and (forecastYear,
    // forecastValue) — the only two real data points we have — and fills every
    // other year in between/around them via CAGR compounding off the base year,
    // so historic years extrapolate backward and forecast years forward.
    private function buildYearlySeries(array $years, $baseYear, $forecastYear, $baseValue, $forecastValue)
    {
        $span = $forecastYear - $baseYear;
        $cagr = ($span !== 0 && $baseValue > 0) ? pow($forecastValue / $baseValue, 1 / $span) - 1 : 0.0;

        $series = [];
        foreach ($years as $year) {
            if ($year === $baseYear) {
                $series[$year] = $baseValue;
            } elseif ($year === $forecastYear) {
                $series[$year] = $forecastValue;
            } else {
                $series[$year] = $baseValue * pow(1 + $cagr, $year - $baseYear);
            }
        }

        return $series;
    }

    private function renderBarChartSvg($keyword, array $years, array $series, $unit, $historicYear, $baseYear, $forecastYear, $cagr)
    {
        $fontFamily = 'Roboto,Roboto_MSFontService,sans-serif';
        $barColor = '#156082';
        $axisColor = '#D9D9D9';
        $labelColor = '#595959';
        $valueLabelColor = '#404040';

        $chartLeft = 100;
        $chartRight = 1180;
        $baselineY = 620;
        $chartTop = 150;
        $maxBarHeight = $baselineY - $chartTop;

        $count = max(1, count($years));
        $slotWidth = ($chartRight - $chartLeft) / $count;
        $barWidth = $slotWidth * 0.55;
        $yearFontSize = (int) max(9, min(14, floor($slotWidth * 0.32)));

        $maxValue = !empty($series) ? max($series) : 0.0;

        $title = "{$keyword} Market Size, {$historicYear}-{$forecastYear} (USD {$unit})";
        $titleBlock = build_wrapped_center_text($title, 640, 60, $fontFamily, 700, 26, 1100);

        $bars = '';
        $i = 0;
        foreach ($years as $year) {
            $value = $series[$year] ?? 0.0;
            $barHeight = $maxValue > 0 ? ($value / $maxValue) * $maxBarHeight : 0.0;

            $x = $chartLeft + $i * $slotWidth + ($slotWidth - $barWidth) / 2;
            $y = $baselineY - $barHeight;
            $centerX = round($x + $barWidth / 2, 2);

            $bars .= '<rect x="' . round($x, 2) . '" y="' . round($y, 2) . '" width="' . round($barWidth, 2)
                . '" height="' . round($barHeight, 2) . '" fill="' . $barColor . '"/>';

            $bars .= '<text text-anchor="middle" fill="' . $labelColor . '" font-family="' . $fontFamily
                . '" font-weight="400" font-size="' . $yearFontSize . '" x="' . $centerX . '" y="' . ($baselineY + 24) . '">'
                . e((string) $year) . '</text>';

            // Only the two real data points (base year, forecast year) get a value label
            if ($year === $baseYear || $year === $forecastYear) {
                $labelText = number_format($value, 2) . $unit;
                $bars .= '<text text-anchor="middle" fill="' . $valueLabelColor . '" font-family="' . $fontFamily
                    . '" font-weight="700" font-size="15.96" x="' . $centerX . '" y="' . round($y - 10, 2) . '">'
                    . e($labelText) . '</text>';
            }

            $i++;
        }

        $cagrLine = build_wrapped_center_text(
            'CAGR: ' . number_format($cagr, 1) . '% (' . $baseYear . '-' . $forecastYear . ')',
            640,
            98,
            $fontFamily,
            400,
            16,
            1100
        );

        return '<svg width="100%" height="100%" viewBox="0 0 1280 720" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" overflow="hidden">'
            . '<rect x="0" y="0" width="1280" height="720" fill="#FFFFFF"/>'
            . '<rect x="2" y="2" width="1276" height="716" fill="none" stroke="' . $axisColor . '" stroke-width="3"/>'
            . $titleBlock
            . $cagrLine
            . '<line x1="' . $chartLeft . '" y1="' . $baselineY . '" x2="' . $chartRight . '" y2="' . $baselineY
            . '" stroke="' . $axisColor . '" stroke-linejoin="round" stroke-miterlimit="10" fill="none"/>'
            . $bars
            . '<text fill="' . $labelColor . '" font-family="' . $fontFamily . '" font-weight="400" font-size="16" x="' . $chartLeft . '" y="670">'
            . 'Source: www.bremontstrategy.com</text>'
            . '</svg>';
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
}
