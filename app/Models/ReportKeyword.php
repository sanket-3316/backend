<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportKeyword extends Model
{
    protected $fillable = [
        'keyword',
        'is_report_generated',
        'report_status',
        'error'
    ];
}