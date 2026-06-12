<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $blogId = DB::table('blogs')->insertGetId([
            'blog_slug' => 'future-of-electric-vehicles',
            'author' => 1,
            'report_id' => 1,
            'thumbnail' => 'blog-ev.jpg',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blogs = [
            1 => [
                'title' => 'Future of Electric Vehicles',
                'desc' => 'Electric vehicles are transforming mobility.',
            ],
            2 => [
                'title' => '電気自動車の未来',
                'desc' => '電気自動車はモビリティを変革しています。',
            ],
            3 => [
                'title' => '전기차의 미래',
                'desc' => '전기차는 이동성을 변화시키고 있습니다.',
            ],
        ];

        foreach ($blogs as $langId => $data) {
            DB::table('blog_descriptions')->insert([
                'blog_id' => $blogId,
                'language_id' => $langId,
                'meta_title' => $data['title'],
                'meta_desc' => $data['desc'],
                'meta_keyword' => Str::slug($data['title']),
                'description' => $data['desc'] . ' Detailed blog content goes here.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}