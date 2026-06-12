<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reportId = DB::table('reports')->insertGetId([
            'report_url' => 'global-ev-market',
            'category_id' => 2, // automotive
            'base_year_market_size' => '500 Billion USD',
            'forecast_year_market_size' => '1200 Billion USD',
            'forecast_cagr' => '12.5%',
            'market_share_analysis' => 'Top players dominate EV segment globally.',
            'regional_market_intelligence_analysis' => 'Asia-Pacific leads growth.',
            'top_countries_insights_analysis' => 'China, USA, Germany key markets.',
            'base_year' => '2024',
            'historic_year' => '2019-2023',
            'forecast_year' => '2025-2032',
            'pages' => 250,
            'views' => 120,
            'rating' => 4.7,
            'author' => 1,
            'formats' => 'PDF, Excel',
            'is_translated_all_lang' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Languages: 1=en, 2=ja, 3=ko
        $languages = [
            1 => 'Global EV Market Report',
            2 => '世界の電気自動車市場レポート',
            3 => '글로벌 전기차 시장 보고서'
        ];

        foreach ($languages as $langId => $title) {

            $infoId = DB::table('reports_info')->insertGetId([
                'report_id' => $reportId,
                'language_id' => $langId,
                'report_title' => $title,
                'meta_desc' => 'EV market trends and forecast analysis',
                'h1_long_title' => $title . ' 2025-2032',
                'keyword' => Str::slug($title),
                'key_market_trends' => 'EV adoption increasing globally.',
                'thumbnail' => 'ev-market.jpg',
                'unique_id' => uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('report_descriptions')->insert([
                'info_id' => $infoId,
                'description' => 'Detailed analysis of electric vehicle market.',
                'segmentation' => 'By vehicle type, battery, region.',
                'primary_interview_insights' => 'Insights from industry experts.',
                'top_ten_company_details' => 'Tesla, BYD, Volkswagen, etc.',
            ]);
        }

        // Pricing
        $prices = [
            ['type' => 'single_user', 'price' => 1200],
            ['type' => 'multi_user', 'price' => 2500],
            ['type' => 'corporate', 'price' => 5000],
            ['type' => 'excel_datapack', 'price' => 800],
        ];

        foreach ($prices as $p) {
            DB::table('report_prices')->insert([
                'report_id' => $reportId,
                'price_type' => $p['type'],
                'price' => $p['price'],
                'currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}