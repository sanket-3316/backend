<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'designation',
        'message',
        'report_id',
        'category_id',
        'status_id',
    ];
}
