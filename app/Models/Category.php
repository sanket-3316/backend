<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Category extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'slug',
        'thumbnail',
        'sort_order'
    ];

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public static function getCategoriesWithTranslation($languageId = 1, $excludeId = null)
    {
        return DB::table('categories as c')
            ->join('category_translations as ct', function ($join) use ($languageId) {
                $join->on('c.id', '=', 'ct.category_id')
                    ->where('ct.language_id', $languageId);
            })
            ->whereNull('c.deleted_at')
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('c.id', '!=', $excludeId);
            })
            ->select(
                'c.id',
                'c.slug',
                'ct.name'
            )
            ->orderBy('ct.name', 'asc') // better UX
            ->get();
    }
}
