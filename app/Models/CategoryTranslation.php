<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategoryTranslation extends Model
{
    public $timestamps = false; // ✅ ADD THIS

    protected $fillable = [
        'category_id',
        'language_id',
        'name',
        'meta_title',
        'meta_description',
        'description'
    ];
    // ✅ ADD THIS RELATION
    public function language()
    {
        return $this->belongsTo(Languages::class, 'language_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
  
}
