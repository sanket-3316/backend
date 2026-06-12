<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Agriculture', 'slug' => 'agriculture'],
            ['name' => 'Automotive', 'slug' => 'automotive'],
            ['name' => 'Chemicals & Materials', 'slug' => 'chemicals-materials'],
            ['name' => 'Consumer Goods', 'slug' => 'consumer-goods'],
            ['name' => 'Energy And Mining', 'slug' => 'energy-and-mining'],
            ['name' => 'Food And Beverages', 'slug' => 'food-and-beverages'],
            ['name' => 'Healthcare', 'slug' => 'healthcare'],
            ['name' => 'Manufacturing', 'slug' => 'manufacturing'],
        ];

        foreach ($categories as $cat) {
            $categoryId = DB::table('categories')->insertGetId([
                'slug' => $cat['slug'],
            ]);

            DB::table('category_translations')->insert([
                'category_id' => $categoryId,
                'language_id' => 1,
                'name' => $cat['name'],
            ]);
        }
    }
}
