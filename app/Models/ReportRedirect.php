<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRedirect extends Model
{
    protected $table = 'report_redirect';

    protected $fillable = [
        'redirect_from',
        'redirect_to',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
