<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('languages')->insert([
            ['name' => 'English', 'code' => 'en', 'is_default' => 1],
            ['name' => 'Japanese', 'code' => 'ja', 'is_default' => 0],
            ['name' => 'Korean', 'code' => 'ko', 'is_default' => 0],
        ]);
    }
}
