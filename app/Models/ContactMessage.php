<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'company',
        'message',
        'is_deleted',
    ];
}
