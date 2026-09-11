<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Seed (or refresh) the supported languages. Safe to re-run — keyed on
     * the unique `code` column, so existing rows (and their ids, which other
     * tables reference via language_id) are updated in place, not duplicated.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'English',  'code' => 'en', 'is_default' => 1],
            ['name' => 'Japanese', 'code' => 'ja', 'is_default' => 0],
            ['name' => 'Korean',   'code' => 'ko', 'is_default' => 0],
            ['name' => 'Chinese',  'code' => 'zh', 'is_default' => 0],
            ['name' => 'Spanish',  'code' => 'es', 'is_default' => 0],
            ['name' => 'German',   'code' => 'de', 'is_default' => 0],
            ['name' => 'French',   'code' => 'fr', 'is_default' => 0],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->updateOrInsert(
                ['code' => $language['code']],
                [
                    'name'       => $language['name'],
                    'is_default' => $language['is_default'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
