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

function report_years($baseYear = 2025)
{
    $historicStart = $baseYear - 7;   // 2017
    $historicEnd   = $baseYear - 1;   // 2023

    $forecastStart = $baseYear + 1;   // 2025
    $forecastEnd   = $baseYear + 9;   // 2033

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
        'pages' => rand(150, 200),
        'views' => rand(40, 50),
        'rating' => number_format(rand(40, 50) / 10, 1) // 4.0 - 5.0
    ];
}

function generate_report_title($keyword)
{
    return "$keyword Market Research Report 2033";
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



//  get only segments
function get_report_segmentation_prompt()
{
    return [
        "system" => "You are a market research analyst. Always return structured JSON only. Do not include explanations or extra text.",
        "user" => "Generate detailed market segmentation for the given [[keyword]] market.

                        Return ONLY valid JSON in this format:

                        {
                            \"segments\": {
                                \"By component\": [],
                                \"By deployment\": [],
                                \"By application\": [],
                                \"By industry\": [],
                                \"By region\": []

                            },
                            \"h1_long_title\": \"h1_long_title\"
                        }

                        Rules:
                        - Use realistic market research categories
                        - Minimum 4-6 items per segment
                        - Keep names professional and industry-standard
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

function get_report_market_size_data_prompt()
{
    return [
        "system" => "You are a market research data analyst. Provide realistic estimated numerical market data in JSON format only.",
        "user" => "Generate estimated market size data for the given [[market]] market.\n
        

                Return ONLY valid JSON:

                {
                \"market\": \"\",
                \"base_year\": \"2024\",
                \"base_year_market_size_usd_billion\": 0,
                \"forecast_year\": \"2030\",
                \"forecast_market_size_usd_billion\": 0,
                \"cagr_percent\": 0,
                \"meta_description\": \"\"
                }

                Rules:
                - Use realistic industry estimates
                - CAGR must be logical based on market growth
                - Values should be numbers (not strings)
                - Do not add explanations
                - Do not include currency symbols
                - Do not return anything except JSON

                Market: %s"
    ];
}

function get_report_description_prompt($keyword, $segments, $market, $players)
{
    return [
        "system" => "You are a senior market research analyst. Generate ONLY structured HTML content. Do not add explanations. Follow exact HTML tags and structure.",

        "user" => "
                    Generate a detailed market research report in HTML format using the following data.

                    MARKET: {$keyword}

                    MARKET DATA:
                    - Base Year: {$market['base_year']}
                    - Base Market Size: {$market['base_year_market_size_usd_billion']} Billion USD
                    - Forecast Year: {$market['forecast_year']}
                    - Forecast Market Size: {$market['forecast_market_size_usd_billion']} Billion USD
                    - CAGR: {$market['cagr_percent']}%

                    KEY PLAYERS:
                    " . implode(', ', $players['key_players']) . "

                    SEGMENTS:
                    " . json_encode($segments['segments']) . "

                    ==================== OUTPUT STRUCTURE ====================

                    1. START WITH:
                     <h2 class='market-outlook'>{$keyword} Market Outlook </h2> Write a comprehensive paragraph stating the {$keyword} market was valued at <strong>$ billion in 2025</strong> and is projected to reach <strong>$ billion by 2034</strong>, growing at a <strong>CAGR of %</strong> during the forecast period 2026-2034. I need Actual market sizes here and large paragraph.
                    <div>
                    <h2>Key Takeaways</h2>

                    <ul>
                    <li>Use real market numbers from provided data</li>
                    <li>Include 7-8 bullet points like market size, CAGR, region dominance, drivers, key companies</li>
                    </ul>
                    </div>

                    ---------------------------------------------------------

                    2. <h2>{$keyword} Market Outlook 2025-2034</h2>
                    - Write 2 large paragraphs (detailed, analytical, professional)

                    ---------------------------------------------------------

                    3. <h2>{$keyword} Market Regional Outlook 2025-2034</h2>
                    - Write 2-3 large paragraphs
                    - Cover regions like North America, Europe, Asia-Pacific

                    ---------------------------------------------------------

                    4. <h2>Key Growth Drivers of the {$keyword} Market</h2>
                    - Each driver in separate <div>
                    - Each div must contain:
                    <h3>Driver Title</h3>
                    <p>Large paragraph explanation</p>

                    ---------------------------------------------------------

                    5. <h2>{$keyword} Market Segment Analysis</h2>
                    - Use given segmentation
                    - For each parent segment:
                    <h3>Segment Name</h3>
                    <p>Paragraph 1</p>
                    <p>Paragraph 2</p>

                    ---------------------------------------------------------

                    6. <h2>{$keyword} Segment Comparison</h2>
                    - Create HTML table
                    - 8-10 rows
                    - Columns: Segment | Market Share | Growth Rate | Key Insight
                    - Keep text short

                    ---------------------------------------------------------

                    7. <h2>{$keyword} Market Opportunities and Threats 2025-2034</h2>
                    - 4 large paragraphs (mix opportunities + risks)

                    ---------------------------------------------------------

                    8. <h2>{$keyword} Market Competitor Outlook 2025-2034</h2>
                    - Use key players data
                    - 3 large paragraphs

                    ---------------------------------------------------------

                    9. <h2>{$keyword} Market Latest Industry Developments</h2>
                    - 10-15 points

                    Example:
                    <ul>
                    <li>Company X launched...<li>
                    </ul>

                    ---------------------------------------------------------

                    IMPORTANT RULES:
                    - Use ONLY HTML tags: h2, h3, p, ul, li, table, div
                    - Do NOT use markdown
                    - Do NOT add explanations
                    - Keep paragraphs large and detailed
                    - Make content look like premium market research report

                    Return ONLY HTML.
                    "
    ];
}
