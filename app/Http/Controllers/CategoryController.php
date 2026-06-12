<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $languages = Languages::all();
        $categories = Category::with('translations')->get();
        return view('category.dashboard', compact('languages', 'categories'));
    }

    public function list()
    {
        $defaultLang = Languages::where('code', 'en')->first();

        $categories = Category::with(['translations' => function ($q) use ($defaultLang) {
            $q->where('language_id', $defaultLang->id);
        }])->get();

        $data = $this->buildTree($categories);

        return response()->json(['data' => $data]);
    }
    private function buildTree($categories, $parent_id = null, $level = 0)
    {
        $data = [];

        foreach ($categories->where('parent_id', $parent_id) as $cat) {

            $name = optional($cat->translations->first())->name;
            $name = str_repeat('— ', $level) . $name;

            $data[] = [
                'id' => '
                <button class="btn-outline-custom btn-outline-primary-gradient btn-floating-sm expandLang" data-id="' . $cat->id . '">+</button>
            ',
                'name' => $name,
                'slug' => $cat->slug,
                'action' => '
                        <button class="btn-outline-custom btn-outline-primary-gradient btn-floating-sm editCategory" data-id="' . $cat->id . '">
                            <i class="fa fa-pen"></i>
                        </button>

                        <button class="btn-outline-custom btn-outline-warning-gradient btn-floating-sm deleteCategory" data-id="' . $cat->id . '">
                            <i class="fa fa-trash"></i>
                        </button>
                    '
            ];

            $data = array_merge($data, $this->buildTree($categories, $cat->id, $level + 1));
        }

        return $data;
    }

    public function getTranslations($id)
    {
        $translations = CategoryTranslation::with(['language', 'category']) // ✅ add category
            ->where('category_id', $id)
            ->get();

        return response()->json($translations);
    }


    private function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (Category::where('slug', $slug)
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists()
        ) {

            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $defaultLang = Languages::where('code', 'en')->first();

        $validator = Validator::make($request->all(), [
            'language_id' => 'required|exists:languages,id',
            'name' => 'required|string|max:255',

            // ✅ Required only for translation
            'base_category_id' => [
                Rule::requiredIf($request->language_id != $defaultLang->id)
            ],

            // ✅ Required only for English
            'slug' => [
                Rule::requiredIf($request->language_id == $defaultLang->id)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            // =====================================================
            // ✅ ENGLISH CATEGORY (CREATE NEW CATEGORY)
            // =====================================================
            if ($request->language_id == $defaultLang->id) {

                // ✅ generate unique slug
                $slug = $this->generateUniqueSlug($request->slug ?? $request->name);

                // ✅ create category
                $category = Category::create([
                    'slug' => $slug,
                    'parent_id' => $request->parent_id,
                    'thumbnail' => $request->thumbnail,
                    'sort_order' => $request->sort_order ?? 0,
                ]);

                // ✅ create translation
                CategoryTranslation::create([
                    'category_id' => $category->id,
                    'language_id' => $request->language_id,
                    'name' => $request->name,
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'description' => $request->description,
                ]);
            }

            // =====================================================
            // ✅ OTHER LANGUAGE (ONLY TRANSLATION)
            // =====================================================
            else {

                // ✅ check duplicate
                $exists = CategoryTranslation::where([
                    'category_id' => $request->base_category_id,
                    'language_id' => $request->language_id
                ])->exists();

                if ($exists) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This language already exists for selected category'
                    ]);
                }

                // ✅ create translation only
                CategoryTranslation::create([
                    'category_id' => $request->base_category_id,
                    'language_id' => $request->language_id,
                    'name' => $request->name,
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'description' => $request->description,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Saved successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage() // for debugging
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $defaultLang = Languages::where('code', 'en')->first();

        $validator = Validator::make($request->all(), [
            'language_id' => 'required|exists:languages,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $category = Category::findOrFail($id);

            // =====================================================
            // ✅ ONLY IF ENGLISH → ALLOW SLUG UPDATE
            // =====================================================
            if ($request->language_id == $defaultLang->id) {

                // ✅ ONLY update slug if user provided it
                if ($request->filled('slug')) {

                    // only regenerate if slug changed
                    if ($request->slug != $category->slug) {

                        $slug = $this->generateUniqueSlug($request->slug, $id);

                        $category->slug = $slug;
                    }
                }

                $category->parent_id = $request->parent_id;
                $category->thumbnail = $request->thumbnail;
                $category->sort_order = $request->sort_order ?? 0;

                $category->save();
            }

            // =====================================================
            // ✅ UPDATE TRANSLATION (ALL LANGUAGES)
            // =====================================================
            CategoryTranslation::updateOrCreate(
                [
                    'category_id' => $id,
                    'language_id' => $request->language_id
                ],
                [
                    'name' => $request->name,
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'description' => $request->description,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        Category::find($request->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deleted'
        ]);
    }


    public function englishList()
    {
        $defaultLang = Languages::where('code', 'en')->first();

        $categories = Category::with(['translations' => function ($q) use ($defaultLang) {
            $q->where('language_id', $defaultLang->id);
        }])->get();

        $data = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => optional($cat->translations->first())->name
            ];
        });

        return response()->json($data);
    }
    public function getAllTranslations($id)
    {
        $defaultLang = Languages::where('code', 'en')->first();

        $translations = CategoryTranslation::with('language')
            ->where('category_id', $id)
            ->where('language_id', '!=', $defaultLang->id) // ✅ EXCLUDE ENGLISH
            ->get();

        return response()->json($translations);
    }

    public function deleteTranslation(Request $request)
    {
        CategoryTranslation::find($request->id)->delete();

        return response()->json([
            'status' => true
        ]);
    }
//  ============================================================================
    // **********************  API **********************
// ============================================================================

    public function get_all_categories(Request $request)
    {
        try {
            // 🔹 Get header
            $localeHeader = $request->header('Accept-Language');

            if (!$localeHeader) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accept-Language header is missing.'
                ], 400);
            }

            // 🔹 Extract language (en-US → en)
            $locale = substr($localeHeader, 0, 2);

            // 🔹 Fetch language
            $language = Languages::where('code', $locale)->first();

            if (!$language) {
                $language = Languages::where('code', 'en')->first();
            }

            // 🔹 Fetch categories
            $categories = Category::query()
                ->join('category_translations as ct', function ($join) use ($language) {
                    $join->on('categories.id', '=', 'ct.category_id')
                        ->where('ct.language_id', $language->id);
                })
                ->whereNull('categories.deleted_at')
                ->select(
                    'categories.id',
                    'categories.slug',
                    'categories.thumbnail',
                    'ct.name'
                )
                ->orderBy('ct.name', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'categories' => $categories
            ]);
     
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $err->getMessage() // remove in production
            ], 500);
        }
    }
}
