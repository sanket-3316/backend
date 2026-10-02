<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prompt extends Model
{
    protected $fillable = [
        'name',
        'type',
        'system_prompt',
        'user_prompt',
        'temperature',
        'max_tokens',
        'response_format',
        'is_active',
    ];

    // Report generation cron: the one active prompt of this type. Multiple
    // active rows are allowed (kept simple like the rest of this table — no
    // category/scope targeting), the most recently updated one wins.
    public static function getActivePrompt($type = 'report')
    {
        return DB::table('prompts')
            ->where('type', $type)
            ->where('is_active', 1)
            ->orderByDesc('updated_at')
            ->first();
    }
}
