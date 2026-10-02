<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromptSeeder extends Seeder
{
    /**
     * Seeds the single default "report" type prompt the generate cron uses.
     * Safe to re-run — keyed on name+type, updates in place rather than
     * duplicating. Admins can edit the text afterwards from /prompts;
     * re-running this seeder will NOT overwrite a prompt they've since
     * customized unless it still has this exact default name.
     */
    public function run(): void
    {
        $systemPrompt = "You are a senior market research analyst who writes premium, paid market research reports "
            . "(the same quality/format bought from firms like MarketsandMarkets, Grand View Research, or Bremont Strategy). "
            . "Generate ONLY a single valid JSON object as your entire response — no explanations, no markdown code fences. "
            . "The market size, CAGR, segmentation, and key companies are given to you below as established facts — "
            . "do not invent different ones, do not second-guess them, just use them exactly as given throughout the report.";

        $userPrompt = <<<'PROMPT'
Generate a complete market research report for the [[keyword]] market using the data below, which is already finalized — use it exactly as given, do not invent or alter it.

MARKET DATA (already finalized — use exactly as given):
- Base Year: [[base_year]] — Market Size: [[base_year_market_size]]
- Forecast Year: [[forecast_year]] — Market Size: [[forecast_year_market_size]]
- CAGR: [[forecast_cagr]]%
- Historic Period: [[historic_period]]
- Forecast Period: [[forecast_period]]

SEGMENTS (JSON — category name => sub-segment names, already finalized, use these EXACT names, do not invent, rename, or add categories):
[[segments]]

KEY COMPANIES (already finalized, use these exact names):
[[companies]]

[[category_instruction]]

Return ONLY a valid JSON object with these exact keys:
{
  "description": "<the full HTML report body — see OUTPUT STRUCTURE below>",
  "meta_desc": "<150-160 character SEO meta description for this report, mentioning the market size and forecast year>",
  "primary_interview_insights": "<the FAQ section HTML — see FAQ STRUCTURE below>"[[category_json_key]]
}

==================== "description" OUTPUT STRUCTURE (follow in this exact order) ====================

1. <h2>[[keyword]] Market Outlook</h2>
Write 5-6 detailed, analytical paragraphs (<p> each), in this order:
- Paragraph 1 (required, use <strong> on the numbers): state the [[keyword]] market was valued at <strong>[[base_year_market_size]] in [[base_year]]</strong> and is projected to reach <strong>[[forecast_year_market_size]] by [[forecast_year]]</strong>, growing at a <strong>CAGR of [[forecast_cagr]]%</strong> during the forecast period [[forecast_period]]. Explain briefly why (1-2 sentences).
- Paragraph 2: characterize the market and the core technology/product/service — what it is, how it works, what makes it distinct from alternative/conventional approaches.
- Paragraph 3: what is driving overall demand (structural/industry-level forces).
- Paragraph 4: technology development trends and where R&D/product focus is heading.
- Paragraph 5: competitive dynamics — how vendors compete (price, technical performance, distribution, support) and what most influences adoption.
- Paragraph 6 (optional): any additional market-specific nuance worth covering.

---------------------------------------------------------

2. <h2>[[keyword]] Market Key Takeaways</h2>
<ul> with 4-5 <li> bullets. Each bullet is a full, data-rich sentence (not a fragment) using the real numbers given above — market size/CAGR, a leading segment's share and growth rate, the leading region's share, and a demand driver. No generic bullets.

---------------------------------------------------------

3. <h2>[[keyword]] Market Key Drivers</h2>
First, an HTML <table> with header row <th>Drivers</th><th>Impact</th>, one row per driver, 4-5 rows total. Driver names short (2-5 words), Impact is one concise sentence.
Then, for EACH driver row in that table, in the same order, output:
<h3>{Driver Name}</h3>
<p>One paragraph (60-100 words) explaining that specific driver's mechanism and effect on demand.</p>

---------------------------------------------------------

4. <h2>[[keyword]] Market Opportunities and Challenges</h2>
First, an HTML <table> with header row <th>Opportunity</th><th>Challenges</th>, 3 rows pairing one opportunity with one challenge per row (short phrases, 2-5 words each).
Then exactly 2 paragraphs: one (100-150 words) elaborating the opportunities, one (100-150 words) elaborating the challenges/threats.

---------------------------------------------------------

5. <h2>[[keyword]] Market Report Scope</h2>
A single 2-column HTML <table> (header <th>Attributes</th><th>Details</th>) with one row per attribute, in this order:
- Report Title: "[[keyword]] Market Research Report [[forecast_year]]"
- One row PER segmentation category given above, using its exact name as the attribute, value = comma-separated list of its exact sub-segment names
- Regions Covered: North America, Europe, Asia Pacific, Latin America, Middle East & Africa
- Countries Covered: realistic countries grouped by the regions above
- Base Year: [[base_year]]
- Historic Data: [[historic_period]]
- Forecast Period: [[forecast_period]]
- Number of Pages: a realistic number between 250 and 320
- Number of Tables & Figures: a realistic number between 300 and 450
- Customization Available: "Yes, the report can be customized as per your need."
No paragraphs in this section — table only.

---------------------------------------------------------

6. <h2>[[keyword]] Market Segment Analysis</h2>
One short intro paragraph (2-3 sentences) previewing the segmentation categories given above.

Then, for EACH segment category given above, in the same order, output a separate block:
<h2>{Segment Category Name} Analysis</h2> — the heading text MUST be exactly the category name followed by the single word " Analysis" and nothing else (no "Market", no extra words).
Then an HTML <table> (header <th>Segment</th><th>Market Share</th><th>Growth Rate</th><th>Key Insight</th>) with exactly one row per sub-segment name in that category (use the exact sub-segment names given above, realistic share %/growth rate that sum sensibly, short key insight phrase).
Then 2-3 paragraphs (80-120 words each) analyzing the sub-segments in that category.

---------------------------------------------------------

7. <h2>[[keyword]] Market Regional Outlook</h2>
First, a 2-column HTML <table> (header <th>Regional Outlook</th><th></th>) with 4 rows: "Largest Market" => region name, "Fastest Growing Market" => region name, "Emerging Countries" => 3 country names, "Future Outlook" => one-sentence summary.
Then exactly 5 paragraphs (80-120 words each), one per region in this order: North America, Europe, Asia-Pacific, Latin America, Middle East & Africa.

---------------------------------------------------------

8. <h2>[[keyword]] Market Competitor Outlook</h2>
A 2-column HTML <table> (header <th>Competitor Outlook</th><th></th>) with 3 rows:
- "Market Leader" => a <ul><li> with exactly 1 company (pick the most prominent from the KEY COMPANIES given above)
- "Key Players" => a <ul><li> list of the rest of the KEY COMPANIES given above
- "Key Competitive Factors" => a <ul><li> list of 4-5 short factor names
No paragraphs in this section — table only.

---------------------------------------------------------

9. <h2>[[keyword]] Pricing Analysis</h2>
Exactly 2 paragraphs (100-150 words each, no table) on pricing factors and price sensitivity.

---------------------------------------------------------

10. <h2>[[keyword]] Consumer Buying Behaviour / Preference Analysis</h2>
<h3>Key Purchase Criteria</h3>
An HTML <table> (header <th>Buying Factor</th><th>Relative Importance</th><th>Reason</th>) with 5 rows.
Then exactly 2 paragraphs (100-150 words each) on who the typical buyers are and what most influences their purchase decision.

==================== "primary_interview_insights" (FAQ) STRUCTURE ====================

<h2>Frequently Asked Questions</h2>
Followed by exactly 6-8 question/answer pairs, each as:
<h3>{Question}?</h3>
<p>{Answer — 1-3 sentences, specific and data-driven using the numbers given above}</p>

Cover: current market size, projected size by [[forecast_year]], CAGR, leading region, leading segment, key companies, key growth drivers, and whether the report is customizable (answer: yes).

==================== RULES ====================
- Use ONLY these HTML tags inside "description" and "primary_interview_insights": h2, h3, p, ul, li, table, thead, tbody, tr, th, td, strong
- Do NOT use markdown, do NOT use div
- Every <h2>/<h3> heading text must be EXACTLY as specified above
- Paragraphs must be substantive and specific to [[keyword]], never generic filler
- Do not return anything except the single JSON object described above
PROMPT;

        DB::table('prompts')->updateOrInsert(
            ['name' => 'Default Report Prompt', 'type' => 'report'],
            [
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
                'temperature' => 0.4,
                'max_tokens' => 14000,
                'response_format' => 'json_object',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
