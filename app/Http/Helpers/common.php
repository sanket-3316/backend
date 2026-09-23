<?php

use App\Models\Category;
use Illuminate\Support\Str;

function get_openAI_api_key()
{
    return env('OPENAI_API_KEY');
}

function renderCategoryOptions($categories, $parent_id = null, $level = 0)
{
    foreach ($categories->where('parent_id', $parent_id) as $cat) {
        $indent = str_repeat('— ', $level);
        $name = $cat->translations->first()->name ?? $cat->slug;

        echo '<option value="' . $cat->id . '">' . $indent . $name . '</option>';

        renderCategoryOptions($categories, $cat->id, $level + 1);
    }
}


if (!function_exists('formatDate')) {
    function formatDate($date)
    {
        return \Carbon\Carbon::parse($date)->format('d M Y');
    }
}

if (!function_exists('slugify')) {
    function slugify($text)
    {
        return \Illuminate\Support\Str::slug($text);
    }
}

function categoryName($category)
{
    return optional($category->translations->first())->name;
}

function isActiveRoute($route)
{
    return request()->is($route) ? 'active' : '';
}

function report_years($baseYear = 2026)
{
    $historicStart = $baseYear - 5;   // 2021
    $historicEnd   = $baseYear - 1;   // 2024

    $forecastStart = $baseYear;   // 2025
    $forecastEnd   = $baseYear + 10;   // 2036

    return [
        'base_year' => $baseYear,

        'historic_start_year' => $historicStart,
        'historic_end_year' => $historicEnd,
        'historic_period' => $historicStart . '-' . $historicEnd,

        'forecast_start_year' => $forecastStart,
        'forecast_end_year' => $forecastEnd,
        'forecast_period' => $forecastStart . '-' . $forecastEnd,
    ];
}

function report_random_stats()
{
    return [
        'pages' => rand(300, 500),
        'views' => rand(40, 50),
        'rating' => number_format(rand(40, 50) / 10, 1) // 4.0 - 5.0
    ];
}

if (!function_exists('build_public_report_url')) {
    // Single source of truth for "view this report on the live site" links —
    // used by the admin dashboard and the internal-linking cron, so they
    // never drift apart. Built from config('app.frontend_url'), NOT url()/
    // APP_URL — this app IS the API backend (api.bremontstrategy.com), a
    // different domain from the public frontend the report actually renders
    // on (www.bremontstrategy.com). English has no locale prefix; every
    // other language is prefixed with its code.
    function build_public_report_url($languageCode, $slug)
    {
        $base = rtrim(config('app.frontend_url'), '/');
        $isEnglish = strtolower((string) $languageCode) === 'en';

        return $isEnglish
            ? $base . '/report/' . $slug
            : $base . '/' . $languageCode . '/report/' . $slug;
    }
}

function generate_report_title($keyword)
{
    $report_year = report_years();
    $forecast_end_year = $report_year['forecast_end_year'];
    return "$keyword Market Research Report $forecast_end_year";
}
function generate_report_h1_long_title($keyword, $segments)
{
    return "$keyword Market Report is Segmented " . json_encode($segments) . "and Geography (North America, Europe, Asia-Pacific, South America, and the Middle East and Africa). The Market Forecasts are Provided in Terms of Value (USD)";
}

function search_from_gpt($user_prompt, $system_prompt = "", $temperature = 0.08, $max_token = 614, $response_format = 'text')
{

    $system_prompt = [
        "role" => "system",
        "content" => [['type' => 'text', 'text' => $system_prompt]]
    ];

    $post_array = [
        "model" => "gpt-4o",
        "messages" => [
            $system_prompt,
            [
                "role" => "user",
                "content" => [['type' => 'text', 'text' => $user_prompt]]
            ]
        ],
        "temperature" => $temperature,
        "max_tokens" => $max_token,
        "top_p" => 1,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
        "response_format" => ["type" => $response_format],
    ];

    $curl = curl_init();
    $gpt_open_api_key = get_openAI_api_key();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($post_array),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            "Authorization: Bearer $gpt_open_api_key"
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    if (curl_errno($curl)) {
        return false;
    }
    $response = json_decode($response, true);
    if (isset($response['error'])) {
        return $response['error']['message'] ?? 'An error occurred while processing your request.';
    }

    if (empty($response['choices'][0]['message']['content']) || !$response['choices'][0]['message']['content']) {
        return false;
    }

    $gpt_response = trim($response['choices'][0]['message']['content'], " \t\n\r\0\x08,.");

    return $gpt_response;
}

if (!function_exists('clean_gpt_html_response')) {
    // GPT sometimes wraps the HTML report body in a markdown code fence
    // (```html ... ```). Strip the fence markers so only the HTML is stored.
    // "```html" is removed before "```" so the language tag can't leave a
    // stray "html" behind.
    function clean_gpt_html_response($content)
    {
        if (!is_string($content)) {
            return $content;
        }

        $content = str_ireplace(['```html', '```'], '', $content);

        return trim($content);
    }
}



//  get only segments
function get_report_segmentation_prompt()
{
    return [
        "system" => "You are a market research analyst. Always return structured JSON only. Do not include explanations or extra text.",
        "user" => "Generate detailed market segmentation for the given [[keyword]] market.

                        Return ONLY valid JSON in this format:

                        {
                            \"segments\": {
                                \"By Component\": [],
                                \"By Deployment\": [],
                                \"By Application\": [],
                                \"By Industry\": [],
                                \"By region\": []

                            },
                            \"h1_long_title\": \"h1_long_title\"
                        }

                        Rules:
                        - Use realistic market research categories
                        - Minimum 4-6 items per segment
                        - Keep names professional and industry-standard
                        - Always add first latter capital each segment name.
                        - Add [[keyword]] market segments.

                        - Do not return anything except JSON \n\n

                        Example of a H1 long title is: Generative AI (Gen AI) Market Segments - by Component (Software, Hardware, Services), Application (Healthcare, Finance, Media and Entertainment, Retail, Manufacturing, IT and Telecommunications, and Others), Deployment Mode (On-Premises, Cloud), Enterprise Size (Small and Medium Enterprises, Large Enterprises), End-User (BFSI, Healthcare, Retail and E-commerce, Media and Entertainment, Manufacturing, IT and Telecommunications, and Others), and Region (Asia Pacific, North America, Latin America, Europe, and Middle East & Africa) - The Market Forecasts are Provided in Terms of Value (USD)\n\n

                        Market: %s"
    ];
}

//  get only players
function get_report_key_players_prompt()
{
    return [
        "system" => "You are a market research expert. Return only structured JSON. No explanation.",
        "user" => "List major key players (companies) in the given [[keyword]] market.

                        Return ONLY valid JSON:

                        {
                        key_players: []
                        }

                        Rules:
                        - Include 10-20 globally recognized companies
                        - Use official company names
                        - No descriptions, only names
                        - No duplicates
                        - No extra text

                        Market: %s"
    ];
}

function get_report_market_size_data_prompt($years = null)
{
    $years = $years ?: report_years();

    return [
        "system" => "You are a market research data analyst. Provide realistic estimated market data in JSON format only.",
        "user" => "Generate estimated market size data for the [[keyword]] market, using these exact years — do not invent different years.

                Base Year: {$years['base_year']}
                Historic Period: {$years['historic_period']}
                Forecast Year: {$years['forecast_end_year']}

                Return ONLY valid JSON:

                {
                \"base_year_market_size\": \"\",
                \"forecast_market_size\": \"\",
                \"cagr_percent\": 0,
                \"meta_description\": \"\"
                }

                Rules:
                - Use realistic industry estimates
                - CAGR must be logical based on market growth between {$years['base_year']} and {$years['forecast_end_year']}
                - base_year_market_size and forecast_market_size MUST be formatted strings like \"\$1.5 Billion\" or \"\$850 Million\" — always include the dollar sign and the word Million or Billion, never a bare number
                - make sure above provided market size ($1.5 Billion and $850 Million) is example , so don't use this market size.
                - cagr_percent must be a number (not a string)
                - Do not add explanations
                - Do not return anything except JSON"
    ];
}

// ============================================================================
//  INTERNAL LINKING — cross-link report descriptions by keyword
// ============================================================================

if (!function_exists('apply_internal_linking_to_description')) {
    /**
     * Inserts internal links for other reports' keywords into one report's
     * description HTML. Rules:
     *  - A candidate whose URL is already linked anywhere in the description
     *    is skipped entirely (no duplicate links to the same target).
     *  - At most one link per <p> — a paragraph that already contains any
     *    <a> tag (pre-existing or just added) is left alone.
     *  - A given keyword is linked at most once per description, even across
     *    multiple paragraphs.
     *  - Stops once $maxLinks links have been added.
     *
     * $candidates: array of ['keyword' => string, 'url' => string], already
     * excluding the report's own keyword/URL.
     *
     * Returns [updatedHtml, linksAddedCount].
     */
    function apply_internal_linking_to_description($html, array $candidates, $maxLinks = 5)
    {
        if (empty($html) || empty($candidates)) {
            return [$html, 0];
        }

        // Candidates already linked anywhere in this description — never link them again.
        preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\']/i', $html, $hrefMatches);
        $alreadyLinkedUrls = array_map('rtrim', $hrefMatches[1] ?? []);

        $candidates = array_values(array_filter($candidates, function ($c) use ($alreadyLinkedUrls) {
            return !empty($c['keyword']) && !empty($c['url']) && !in_array(rtrim($c['url']), $alreadyLinkedUrls, true);
        }));

        if (empty($candidates)) {
            return [$html, 0];
        }

        // Longer/more specific keywords win first, so a short keyword can't
        // "steal" a match that belongs to a longer phrase containing it.
        usort($candidates, fn($a, $b) => mb_strlen($b['keyword']) <=> mb_strlen($a['keyword']));

        $added = 0;

        $html = preg_replace_callback('/<p\b[^>]*>.*?<\/p>/is', function ($m) use (&$candidates, &$added, $maxLinks) {
            $paragraph = $m[0];

            if ($added >= $maxLinks) {
                return $paragraph;
            }

            // A paragraph that already has any link is off-limits for a new one.
            if (stripos($paragraph, '<a ') !== false || stripos($paragraph, '<a>') !== false) {
                return $paragraph;
            }

            foreach ($candidates as $i => $candidate) {
                $pattern = '/\b(' . preg_quote($candidate['keyword'], '/') . ')\b/i';

                if (preg_match($pattern, $paragraph, $found)) {
                    $link = '<a href="' . e($candidate['url']) . '">' . $found[1] . '</a>';
                    $paragraph = preg_replace($pattern, $link, $paragraph, 1);

                    $added++;
                    unset($candidates[$i]); // linked once in this description — don't link it again elsewhere
                    break; // only one link per paragraph
                }
            }

            return $paragraph;
        }, $html);

        return [$html, $added];
    }
}

if (!function_exists('parse_market_value')) {
    // Splits a stored market-size string into [numeric value, short unit].
    // Handles both the compact form ("3.5Bn") and the GPT-generated form
    // ("$1.5 Billion"/"$850 Million") — the leading "$" is simply skipped
    // since the number match isn't anchored to the string start — and always
    // normalizes the unit down to "Bn"/"Mn" regardless of which form it saw.
    // Plain numeric input returns [n, '']; empty/unparseable input returns [0.0, ''].
    function parse_market_value($raw)
    {
        if ($raw === null || $raw === '') {
            return [0.0, ''];
        }

        if (is_numeric($raw)) {
            return [(float) $raw, ''];
        }

        preg_match('/(-?[\d,]*\.?\d+)\s*([A-Za-z%$]*)/', (string) $raw, $m);

        $value = isset($m[1]) && $m[1] !== '' ? (float) str_replace(',', '', $m[1]) : 0.0;
        $rawUnit = isset($m[2]) ? trim($m[2]) : '';

        $unit = match (true) {
            $rawUnit === '' => '',
            stripos($rawUnit, 'b') === 0 => 'Bn',
            stripos($rawUnit, 'm') === 0 => 'Mn',
            default => $rawUnit,
        };

        return [$value, $unit];
    }
}

if (!function_exists('get_report_default_prices')) {
    // Single source of truth for default report pricing tiers — used both by
    // the report:generate cron job and by the dashboard's Add Report modal,
    // so the two stay in sync.
    function get_report_default_prices()
    {
        return [
            'single' => 2999,
            'multiuser' => 3999,
            'corporate' => 4999,
            'excel' => 1999,
        ];
    }
}

if (!function_exists('build_wrapped_center_text')) {
    /**
     * Builds a horizontally-centered SVG <text> block that wraps onto extra
     * lines when it's too wide for $maxWidth. There's no font-metrics library
     * here, so line-breaking uses an approximate average-character-width
     * heuristic (~0.6 * font-size for a bold sans-serif) rather than exact
     * rendered text width — centering itself is exact via text-anchor="middle",
     * which the browser/SVG renderer handles natively.
     */
    function build_wrapped_center_text($text, $centerX, $anchorY, $fontFamily, $fontWeight, $fontSize, $maxWidth)
    {
        $avgCharWidth = $fontSize * 0.6;
        $maxChars = max(10, (int) floor($maxWidth / $avgCharWidth));

        $lines = explode("\n", wordwrap((string) $text, $maxChars, "\n", false));

        $lineHeight = $fontSize * 1.3;
        $startY = $anchorY - (count($lines) - 1) * $lineHeight / 2;

        $tspans = '';
        foreach ($lines as $i => $line) {
            $y = round($startY + $i * $lineHeight, 2);
            $tspans .= '<tspan x="' . $centerX . '" y="' . $y . '">' . e($line) . '</tspan>';
        }

        return '<text text-anchor="middle" font-family="' . $fontFamily . '" font-weight="' . $fontWeight . '" font-size="' . $fontSize . '">' . $tspans . '</text>';
    }
}

if (!function_exists('build_wrapped_left_text')) {
    /**
     * Same wrapping approach as build_wrapped_center_text(), but left-anchored
     * at a fixed x — for titles placed against a left margin rather than
     * centered (e.g. the report card thumbnail).
     */
    function build_wrapped_left_text($text, $x, $anchorY, $fontFamily, $fontWeight, $fontSize, $maxWidth, $maxLines = 2)
    {
        $avgCharWidth = $fontSize * 0.6;
        $maxChars = max(6, (int) floor($maxWidth / $avgCharWidth));

        $lines = explode("\n", wordwrap((string) $text, $maxChars, "\n", false));

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $last = $lines[$maxLines - 1];
            $lines[$maxLines - 1] = mb_substr($last, 0, max(0, mb_strlen($last) - 1)) . '…';
        }

        $lineHeight = $fontSize * 1.3;

        $tspans = '';
        foreach ($lines as $i => $line) {
            $y = round($anchorY + $i * $lineHeight, 2);
            $tspans .= '<tspan x="' . $x . '" y="' . $y . '">' . e($line) . '</tspan>';
        }

        return '<text text-anchor="start" font-family="' . $fontFamily . '" font-weight="' . $fontWeight . '" font-size="' . $fontSize . '">' . $tspans . '</text>';
    }
}

// ============================================================================
//  RUNTIME REPORT CHART IMAGES — inject <img> tags into report description HTML
// ============================================================================

if (!function_exists('inject_report_charts_into_description')) {
    /**
     * Adds the runtime chart images (served by ReportImageController) into a
     * report's description HTML at read-time — nothing is persisted to the DB.
     *
     * - The market-overview chart is inserted right above the 2nd <h2> tag.
     * - For every parent segment key (from `segmentation`), if an <h3> tag's
     *   text matches "{segment key} Analysis" (e.g. "By Type Analysis"), a
     *   chart <img> is inserted right after it. Segments with no matching
     *   <h3> are skipped.
     */
    function inject_report_charts_into_description($html, $segmentationJson, $reportSlug)
    {
        if (empty($html) || empty($reportSlug)) {
            return $html;
        }

        $baseUrl = url('/api/report/' . $reportSlug);

        if (preg_match_all('/<h2\b[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE) && count($m[0]) >= 2) {
            $offset = $m[0][1][1];
            $img = '<img src="' . $baseUrl . '/market_overview.webp" alt="Market Overview" loading="lazy" />';
            $html = substr($html, 0, $offset) . $img . substr($html, $offset);
        }

        $segments = is_string($segmentationJson)
            ? (json_decode($segmentationJson, true) ?: [])
            : (is_array($segmentationJson) ? $segmentationJson : []);

        if (!empty($segments)) {
            $segmentKeys = array_keys($segments);

            $html = preg_replace_callback('/<h2\b[^>]*>(.*?)<\/h2>/is', function ($match) use ($segmentKeys, $baseUrl) {
                $text = trim(strip_tags($match[1]));

                foreach ($segmentKeys as $key => $segmentKey) {
                    if (strcasecmp(trim($segmentKey) . ' Analysis', $text) === 0) {
                        $imgSlug = Str::slug($segmentKey);
                        $img = '<img src="' . $baseUrl . '/' . $imgSlug . '.webp" alt="' . e($segmentKey) . '" loading="lazy" style="margin-bottom: 10px;"/>';

                        // if ($key >= 2) {
                        //     continue;
                        // }
                        return $match[0] . $img;
                    }
                }

                return $match[0];
            }, $html);
        }

        return $html;
    }
}

// ============================================================================
//  TABLE OF CONTENTS (TOC) — language-wise template + per-report generation
// ============================================================================

if (!function_exists('resolve_toc_locale')) {
    // Returns the language code whose toc/{code}.json actually exists, falling back to 'en'
    function resolve_toc_locale($languageCode = 'en')
    {
        $languageCode = strtolower($languageCode ?: 'en');

        return file_exists(resource_path("toc/{$languageCode}.json")) ? $languageCode : 'en';
    }
}

if (!function_exists('load_toc_template')) {
    function load_toc_template($languageCode = 'en')
    {
        static $cache = [];

        $languageCode = resolve_toc_locale($languageCode);

        if (isset($cache[$languageCode])) {
            return $cache[$languageCode];
        }

        $path = resource_path("toc/{$languageCode}.json");

        $template = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];

        return $cache[$languageCode] = $template;
    }
}

if (!function_exists('toc_replace')) {
    function toc_replace($value, array $placeholders)
    {
        if (is_array($value)) {
            return array_map(fn($v) => toc_replace($v, $placeholders), $value);
        }

        if (is_string($value)) {
            return str_replace(array_keys($placeholders), array_values($placeholders), $value);
        }

        return $value;
    }
}

if (!function_exists('numbered_toc_section')) {
    // Builds one "X.Y" section, optionally exploding $children into numbered "X.Y.Z" leaves
    function numbered_toc_section($chapterNumber, $sectionIndex, $title, array $children = [])
    {
        $sectionNumber = "{$chapterNumber}.{$sectionIndex}";

        $section = [
            'number' => $sectionNumber,
            'title' => $title,
        ];

        $children = array_values(array_filter($children, fn($c) => is_string($c) && $c !== ''));

        if (!empty($children)) {
            $section['children'] = [];
            foreach ($children as $i => $childTitle) {
                $section['children'][] = [
                    'number' => "{$sectionNumber}." . ($i + 1),
                    'title' => $childTitle,
                ];
            }
        }

        return $section;
    }
}

if (!function_exists('build_report_toc')) {
    /**
     * Generates a report's full, numbered Table of Contents in the requested language.
     *
     * The chapter skeleton (methodology, market overview, region/country list,
     * competitive analysis) always follows resources/toc/{lang}.json verbatim —
     * only [[market_name]] / [[year_range]] placeholders are substituted.
     * Only the "By ..." segment chapters (Ch. 5+) are generated dynamically,
     * one per key found in the report's own `segmentation` data (2, 3, 5+ — whatever
     * that report actually has). "By region"/"By Region" is skipped here since the
     * fixed region/country chapters already cover it.
     */
    function build_report_toc($languageCode, $marketName, $segmentationJson, $historicYear = null, $forecastYear = null, $keyCompanys = null)
    {
        $t = load_toc_template($languageCode);

        if (empty($t)) {
            return null;
        }

        $yearRange = ($historicYear && $forecastYear) ? "{$historicYear}-{$forecastYear}" : '';

        $t = toc_replace($t, [
            '[[market_name]]' => $marketName,
            '[[year_range]]'  => $yearRange,
        ]);

        $meta = $t['meta'] ?? [];

        $segments = is_string($segmentationJson)
            ? (json_decode($segmentationJson, true) ?: [])
            : (is_array($segmentationJson) ? $segmentationJson : []);

        $companies = is_string($keyCompanys)
            ? (json_decode($keyCompanys, true) ?: [])
            : (is_array($keyCompanys) ? $keyCompanys : []);

        $standardTail = fn($n) => [
            numbered_toc_section($n, 2, $meta['trend_line'] ?? ''),
            numbered_toc_section($n, 3, $meta['share_line'] ?? ''),
            numbered_toc_section($n, 4, $meta['yoy_line'] ?? ''),
            numbered_toc_section($n, 5, $meta['attractiveness_line'] ?? ''),
            numbered_toc_section($n, 6, $meta['investment_line'] ?? ''),
            numbered_toc_section($n, 7, $meta['pricing_line'] ?? ''),
            numbered_toc_section($n, 8, $meta['margin_line'] ?? ''),
        ];

        $chapters = [];
        $n = 1;

        $chapters[] = ['number' => (string)$n++, 'title' => $t['chapter_1']['title'] ?? 'Research Methodology'];
        $chapters[] = ['number' => (string)$n++, 'title' => $t['chapter_2']['title'] ?? 'Market Structure'];
        $chapters[] = ['number' => (string)$n++, 'title' => $t['chapter_3']['title'] ?? 'Executive Summary'];

        // Chapter 4 — Market Overview (fixed skeleton, market name already substituted)
        $c4 = $t['chapter_4'] ?? [];
        $sections = [];
        foreach (($c4['sections'] ?? []) as $i => $sec) {
            $sections[] = numbered_toc_section($n, $i + 1, $sec['title'] ?? '', $sec['children'] ?? []);
        }
        $chapters[] = ['number' => (string)$n, 'title' => $c4['title'] ?? '', 'sections' => $sections];
        $n++;

        // Dynamic segment chapters — one per key in this report's own segmentation data
        $segmentLabels = $t['segment_labels'] ?? [];
        $segmentTitleTemplate = $t['segment_chapter']['title'] ?? 'Global [[market_name]] Market, By [[segment_label]]';

        foreach ($segments as $rawKey => $items) {
            if (!is_array($items) || empty($items) || strcasecmp(trim($rawKey), 'By region') === 0) {
                continue;
            }

            $label = $segmentLabels[$rawKey] ?? Str::title(trim(preg_replace('/^By\s+/i', '', $rawKey)));
            $title = str_replace('[[segment_label]]', $label, $segmentTitleTemplate);

            $sections = [
                numbered_toc_section($n, 1, $meta['market_size_line'] ?? '', array_values($items)),
                ...$standardTail($n),
            ];

            $chapters[] = ['number' => (string)$n, 'title' => $title, 'sections' => $sections];
            $n++;
        }

        // Region overview chapter + one fixed chapter per region (countries unchanged from the template)
        $regionChapter = $t['region_chapter'] ?? null;
        if ($regionChapter) {
            $regionNames = array_map(fn($r) => $r['name'], $regionChapter['regions'] ?? []);

            $sections = [
                numbered_toc_section($n, 1, $meta['market_size_line'] ?? '', $regionNames),
                ...$standardTail($n),
            ];
            $chapters[] = ['number' => (string)$n, 'title' => $regionChapter['title'] ?? '', 'sections' => $sections];
            $n++;

            foreach (($regionChapter['regions'] ?? []) as $region) {
                $subTitle = str_replace(
                    '[[region_name]]',
                    $region['name'],
                    $regionChapter['sub_chapter_title'] ?? 'Global [[market_name]] Market, By [[region_name]]'
                );

                $sections = [
                    numbered_toc_section($n, 1, $meta['market_size_line'] ?? '', $region['countries'] ?? []),
                    ...$standardTail($n),
                ];
                $chapters[] = ['number' => (string)$n, 'title' => $subTitle, 'sections' => $sections];
                $n++;
            }
        }

        // Competitive analysis — company list comes from the report's own key_companys
        $competitive = $t['competitive_chapter'] ?? null;
        if ($competitive) {
            $sections = [
                numbered_toc_section($n, 1, $competitive['landscape_title'] ?? '', $competitive['landscape_children'] ?? []),
                numbered_toc_section($n, 2, $competitive['company_profiles_title'] ?? '', array_values($companies)),
            ];
            $chapters[] = ['number' => (string)$n, 'title' => $competitive['title'] ?? 'Competitive Analysis', 'sections' => $sections];
            $n++;
        }

        return [
            'language'    => resolve_toc_locale($languageCode),
            'market_name' => $marketName,
            'chapters'    => $chapters,
            'methodology' => $t['methodology'] ?? null,
        ];
    }
}

if (!function_exists('get_report_translation_prompt')) {
    // Unlike the other get_report_*_prompt() helpers (plain PHP arrays), this
    // one is kept in a JSON file (resources/prompts/report_translation_prompt.json)
    // so the translation wording can be edited without touching code. Returns
    // the same ['system' => ..., 'user' => ...] shape, with [[target_language]]
    // and [[content_json]] left for the caller to fill in.
    function get_report_translation_prompt()
    {
        $path = resource_path('prompts/report_translation_prompt.json');

        if (!file_exists($path)) {
            return ['system' => '', 'user' => ''];
        }

        $prompt = json_decode(file_get_contents($path), true) ?: [];

        return [
            'system' => $prompt['system'] ?? '',
            'user' => $prompt['user'] ?? '',
        ];
    }
}

// Matches the section-by-section structure of the reference sample report
// (resources exports as a PDF the user supplied — full outlook, key
// takeaways, key drivers, opportunities/challenges, report scope, per-segment
// analysis with tables, regional outlook, competitor outlook, pricing
// analysis, and buying-behaviour) so every generated report reads like that
// sample regardless of the market.
//
// The per-segment-category heading MUST be an <h2> reading exactly
// "{Segment Category Name} Analysis" (nothing else) — inject_report_charts_into_description()
// matches on that exact <h2> text to insert that segment's chart image right
// after it, and matches "2nd <h2> tag overall" to insert the market-overview
// chart — see that function's docblock/regex in this same file.
function get_report_description_prompt($keyword, $segments, $market, $players)
{
    $report_year = report_years();
    $base_year =  $report_year['base_year'];
    $forecast_period =  $report_year['forecast_period'];
    $forecast_end_year =  $report_year['forecast_end_year'];
    $segmentKeys = array_keys($segments['segments'] ?? []);
    $segmentHeadingList = implode(', ', array_map(fn($k) => "\"{$k} Analysis\"", $segmentKeys));

    return [
        "system" => "You are a senior market research analyst who writes premium, paid market research reports (the same quality/format bought from firms like MarketsandMarkets, Grand View Research, or Bremont Strategy). Generate ONLY structured HTML content. Do not add explanations, commentary, or markdown. Follow the exact HTML structure, heading text, and paragraph/row counts given — they are hard requirements, not suggestions.",

        "user" => "
                    Generate a complete, detailed market research report body in HTML for the market below, following the EXACT section order, heading text, and content rules in OUTPUT STRUCTURE. This must read like a real, premium, paid market research report — specific, analytical, and numbers-driven, never generic filler.

                    MARKET: {$keyword}

                    MARKET DATA:
                    - Base Year: {$market['base_year']}
                    - Base Market Size: {$market['base_year_market_size']}
                    - Forecast Year: {$market['forecast_year']}
                    - Forecast Market Size: {$market['forecast_market_size']}
                    - CAGR: {$market['cagr_percent']}%
                    - Historic Period: {$report_year['historic_period']}
                    - Forecast Period: {$forecast_period}

                    KEY PLAYERS:
                    " . implode(', ', $players['key_players']) . "

                    SEGMENTS (JSON — category name => sub-segment names, use these EXACT names, do not invent or rename any):
                    " . json_encode($segments['segments']) . "

                    ==================== OUTPUT STRUCTURE (follow in this exact order) ====================

                    1. <h2>{$keyword} Market Outlook</h2>
                    Write 5-6 detailed, analytical paragraphs (<p> each), in this order:
                    - Paragraph 1 (required, use <strong> on the numbers): state the {$keyword} market was valued at <strong>{$market['base_year_market_size']} in {$base_year}</strong> and is projected to reach <strong>{$market['forecast_market_size']} by {$forecast_end_year}</strong>, growing at a <strong>CAGR of {$market['cagr_percent']}%</strong> during the forecast period {$forecast_period}. Explain briefly why (1-2 sentences).
                    - Paragraph 2: characterize the market and the core technology/product/service — what it is, how it works, what makes it distinct from alternative/conventional approaches.
                    - Paragraph 3: what is driving overall demand (structural/industry-level forces).
                    - Paragraph 4: technology development trends and where R&D/product focus is heading.
                    - Paragraph 5: competitive dynamics — how vendors compete (price, technical performance, distribution, support) and what most influences adoption.
                    - Paragraph 6 (optional): any additional market-specific nuance worth covering.

                    ---------------------------------------------------------

                    2. <h2>{$keyword} Market Key Takeaways</h2>
                    <ul> with 4-5 <li> bullets. Each bullet is a full, data-rich sentence (not a fragment) using real numbers derived from the market data and segmentation above — market size/CAGR, a leading segment's share and growth rate, the leading region's share, and a demand driver. No generic bullets. then close </ul> tag.

                    ---------------------------------------------------------

                    3. <h2>{$keyword} Market Key Drivers</h2>
                    First, an HTML <table> with header row <th>Drivers</th><th>Impact</th>, one row per driver, 4-5 rows total. Driver names short (2-5 words, <strong> or <td><strong>), Impact is one concise sentence.
                    Then, for EACH driver row in that table, in the same order, output:
                    <h3>{Driver Name}</h3>
                    <p>One paragraph (60-100 words) explaining that specific driver's mechanism and effect on demand.</p>

                    ---------------------------------------------------------

                    4. <h2>{$keyword} Market Opportunities and Challenges</h2>
                    First, an HTML <table> with header row <th>Opportunity</th><th>Challenges</th>, 3 rows pairing one opportunity with one challenge per row (short phrases, 2-5 words each).
                    Then exactly 2 paragraphs: one paragraph (100-150 words) elaborating the opportunities in the table, one paragraph (100-150 words) elaborating the challenges/threats in the table.

                    ---------------------------------------------------------

                    5. <h2>{$keyword} Market Report Scope</h2>
                    A single 2-column HTML <table class=\"report-scope\"> (header <th>Attributes</th><th>Details</th>) with one row per attribute, in this order:
                    - Report Title: \"{$keyword} Market Research Report {$forecast_end_year}\"
                    - One row PER segmentation category above, using its exact name as the attribute (e.g. row label = the category name from SEGMENTS), value = comma-separated list of its exact sub-segment names
                    - Countries Covered: realistic countries grouped by the regions above (e.g. North America (United States, Canada), Europe (Germany, France, UK, Italy, Spain, Rest of Europe), etc.)
                    - Base Year: {$base_year}
                    - Historic Data: {$report_year['historic_period']}
                    - Forecast Period: {$forecast_period}
                    - Number of Pages: a realistic number between 250 and 320
                    - Number of Tables & Figures: a realistic number between 300 and 450
                    - Customization Available: \"Yes, the report can be customized as per your need.\"
                    No paragraphs in this section — table only.

                    ---------------------------------------------------------

                    6. <h2>{$keyword} Market Segment Analysis</h2>
                    One short intro paragraph (2-3 sentences) previewing the segmentation categories covered.

                    Then, for EACH segment category in SEGMENTS above, in the same order, output a separate block:
                    <h2>{Segment Category Name} Analysis</h2> — the heading text MUST be exactly the category name followed by the single word \" Analysis\" and nothing else (no \"Market\", no extra words). Category names to use, in order: {$segmentHeadingList}.
                    
                    Then 3-4 paragraphs (100-150 words each) analyzing the sub-segments in that category — their relative demand drivers, use cases, and why the leading one leads.
                    - Make sure each para fully detailed and large.
                    - Make sure each <h2> {Segment Category Name} add first latter capital.
                    ---------------------------------------------------------

                    7. <h2>{$keyword} Market Regional Outlook</h2>
                    First, a 2-column HTML <table> (header <th>Regional Outlook</th><th></th>) with 4 rows: \"Largest Market\" => region name, \"Fastest Growing Market\" => region name, \"Emerging Countries\" => 3 country names, \"Future Outlook\" => one-sentence summary.
                    Then exactly 5 paragraphs (80-120 words each), one per region in this order: North America, Europe, Asia-Pacific, Latin America, Middle East & Africa — each covering that region's specific demand drivers, maturity, and outlook.

                    ---------------------------------------------------------

                    8. <h2>{$keyword} Market Competitor Outlook</h2>
                    A 2-column HTML <table> (header <th>Competitor Outlook</th><th></th>) with 3 rows:
                    - \"Market Leader\" => a <ul><li> with exactly 1 company (pick the most prominent from KEY PLAYERS)
                    - \"Key Players\" => a <ul><li> list of 5-8 more companies from KEY PLAYERS
                    - \"Key Competitive Factors\" => a <ul><li> list of 4-5 short factor names (e.g. Product Performance, Technical Support, Distribution Network, Project References, Pricing)
                    No paragraphs in this section — table only.

                    ---------------------------------------------------------

                    9. <h2>{$keyword} Pricing Analysis</h2>
                    Exactly 2 paragraphs (100-150 words each, no table): paragraph 1 on what drives pricing/cost variation in this market (formulation/specification/scale/region), paragraph 2 on price sensitivity vs. value-driven buying behavior.

                    ---------------------------------------------------------

                    10. <h2>{$keyword} Consumer Buying Behaviour / Preference Analysis</h2>
                    <h3>Key Purchase Criteria</h3>
                    An HTML <table> (header <th>Buying Factor</th><th>Relative Importance</th><th>Reason</th>) with 5 rows — realistic buying factors for this market (e.g. performance, durability/reliability, certification/compliance, total cost, differentiating capability), Relative Importance one of Very High/High/Medium-High/Medium, Reason one short phrase.
                    Then exactly 2 paragraphs (100-150 words each) describing who the typical buyers are (roles/titles relevant to this market) and what most influences their purchase decision.

                    ---------------------------------------------------------

                    IMPORTANT RULES:
                    - Use ONLY these HTML tags: h2, h3, p, ul, li, table, thead, tbody, tr, th, td, strong
                    - Do NOT use markdown, do NOT use div
                    - Do NOT add any explanations, headers, or text outside the structure above
                    - Every <h2> heading text must be EXACTLY as specified above — do not add \"Market\" or reword unless explicitly told to
                    - Paragraphs must be substantive and specific to {$keyword}, never generic filler
                    - All tables must use proper <table><thead><tr><th> / <tbody><tr><td> structure

                    Return ONLY HTML — no markdown code fences.
                    "
    ];
}

// Generates the FAQ section stored separately in report_descriptions.primary_interview_insights
// (kept apart from `description` so it can be swapped/edited independently and
// rendered in its own place on the report page).
function get_report_faq_prompt($keyword, $market)
{
    $report_year = report_years();
    $forecast_end_year = $report_year['forecast_end_year'];

    return [
        "system" => "You are a senior market research analyst. Generate ONLY structured HTML content for a report's FAQ section. Do not add explanations or markdown.",

        "user" => "
                    Generate a Frequently Asked Questions section in HTML for the {$keyword} market report.

                    MARKET DATA:
                    - Base Year: {$market['base_year']} — Market Size: {$market['base_year_market_size']}
                    - Forecast Year: {$market['forecast_year']} — Market Size: {$market['forecast_market_size']}
                    - CAGR: {$market['cagr_percent']}%

                    OUTPUT STRUCTURE:
                    <h2>Frequently Asked Questions</h2>
                    Followed by exactly 6-8 question/answer pairs, each as:
                    <h3>{Question}?</h3>
                    <p>{Answer — 1-3 sentences, specific and data-driven where relevant}</p>

                    Cover these topics, one FAQ each (rephrase naturally as real user questions):
                    - What is the current/base year size of the {$keyword} market
                    - What is the projected market size by {$forecast_end_year}
                    - What is the market's CAGR / growth rate during the forecast period
                    - Which region leads / is fastest-growing in this market
                    - Which segment holds the largest share
                    - Who are the key players/companies in this market
                    - What are the key growth drivers
                    - Is the report customizable (answer: yes, the report can be customized as per specific requirements)

                    IMPORTANT RULES:
                    - Use ONLY these HTML tags: h2, h3, p
                    - Do NOT use markdown or div
                    - Do NOT add explanations outside the structure
                    - Answers must be specific to {$keyword}, not generic

                    Return ONLY HTML — no markdown code fences.
                    "
    ];
}
