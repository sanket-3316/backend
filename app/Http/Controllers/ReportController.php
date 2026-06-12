<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Languages;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\UserModel;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $userModel;
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->userModel = new UserModel();
        $this->reportService = $reportService;
    }
    public function index()
    {
        $languages = Languages::all();
        $categories = Category::getCategoriesWithTranslation(1);
        $users = $this->userModel->getAll();
        return view('report.dashboard', compact('languages', 'categories', 'users'));
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'report_title' => 'required',
                'slug' => 'required|unique:reports,report_url',
                'base_year' => 'required',
                'historic_year' => 'required',
                'forecast_year' => 'required',

                'meta_desc' => 'required',
                'h1_long_title' => 'required',
                'keyword' => 'required',

                'description' => 'required',
                'segmentation_json' => 'required',
                // 'primary_interview_insights' => 'required',

                'single' => 'required|numeric',
                'multiuser' => 'required|numeric',
                'corporate' => 'required|numeric',
                'excel' => 'required|numeric',

                // 'thumbnail' => 'required|image'
            ]);

            // Upload image
            $imageName = null;
            if ($request->hasFile('thumbnail')) {
                $imageName = time() . '.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('assets/reports/images'), $imageName);
            }

            $data = $request->all();
            $data['thumbnail'] = $imageName;
            $data['segmentation'] = $request->segmentation_json;

            $result =  Report::createFullReport($data);

            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => "Report added successfully"
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => "Something went wrong"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                "message" => $err->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        $data = Report::getReportById($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        try {

            // ✅ VALIDATION (same as store but slug ignore current)
            $request->validate([
                'report_title' => 'required',
                'slug' => 'required|unique:reports,report_url,' . $id . ',report_id',

                'base_year' => 'required',
                'historic_year' => 'required',
                'forecast_year' => 'required',

                'meta_desc' => 'required',
                'h1_long_title' => 'required',
                'keyword' => 'required',

                'description' => 'required',
                'segmentation_json' => 'required',

                'single' => 'required|numeric',
                'multiuser' => 'required|numeric',
                'corporate' => 'required|numeric',
                'excel' => 'required|numeric',
            ]);

            $data = $request->all();

            // ✅ SEGMENTATION FIX
            $data['segmentation'] = $request->segmentation_json;

            // ✅ FORMAT FIX (checkbox → string)
            $data['format'] = $request->format ?? '';

            // ✅ IMAGE UPLOAD
            if ($request->hasFile('thumbnail')) {
                $imageName = time() . '.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('assets/reports/images'), $imageName);
                $data['thumbnail'] = $imageName;
            } else {
                // keep old image
                $existing = Report::getReportById($id);
                $data['thumbnail'] = $existing->thumbnail ?? null;
            }

            // ✅ UPDATE CALL
            $result = $this->reportService->saveReport($data);


            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => 'Report updated successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Update failed'
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        Report::deleteReport($id);
        return response()->json(['status' => 'deleted']);
    }

    public function getReports()
    {
        $data = Report::getAllReports();

        return response()->json(['data' => $data]);
    }
    public function getReportLanguages($id)
    {
        $data = DB::table('reports_info')
            ->where('report_id', $id)
            ->where('is_deleted', 0)
            ->get();

        return response()->json($data);
    }

    public function deleteReport($id)
    {
        return DB::table('reports_info')
            ->where('report_id', $id)
            ->update(['is_deleted' => 1]);
    }

    //  ============================================================================
    // **********************  API **********************
    // ============================================================================
    public function reports(Request $request)
    {
        try {
            // 🔹 Language from header
            $localeHeader = $request->header('Accept-Language');
            $locale = $localeHeader ? substr($localeHeader, 0, 2) : 'en';

            // 🔹 Get language ID
            $language = Languages::where('code', $locale)->first();

            if (!$language) {
                $language = Languages::where('code', 'en')->first();
            }

            $languageId = $language->id;

            // 🔹 Headers
            $categoryId = $request->header('X-Category-Id');
            $limit = $request->header('X-Limit');

            // 🔥 Default limit logic
            if (empty($categoryId)) {
                $limit = 10; // ✅ latest 10 reports
            } else {
                $limit = $limit ?? 6; // category-wise default
            }

            // 🔥 MAIN QUERY
            $query = DB::table('reports as r')
                ->join('reports_info as ri', function ($join) use ($languageId) {
                    $join->on('r.report_id', '=', 'ri.report_id')
                        ->where('ri.language_id', $languageId);
                })
                ->leftJoin('category_translations as ct', function ($join) use ($languageId) {
                    $join->on('r.category_id', '=', 'ct.category_id')
                        ->where('ct.language_id', $languageId);
                })
                ->select(
                    'r.report_id',
                    'r.report_url',
                    'r.category_id',
                    'ct.name as category_name',
                    'ri.keyword',
                    'ri.meta_desc',
                    'ri.thumbnail'
                )
                ->where('ri.is_deleted', 0)
                ->where('ri.is_publish', 1);

            // 🔹 Category filter
            if (!empty($categoryId)) {
                $query->where('r.category_id', $categoryId);
            }

            // 🔥 Latest reports (when no category)
            if (empty($categoryId)) {
                $query->orderBy('r.created_at', 'desc'); // ✅ latest first
            }

            // 🔹 Limit
            $reports = $query->limit($limit)->get();

            return response()->json([
                'status' => true,
                'data' => $reports
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $err->getMessage()
            ], 500);
        }
    }

    public function categoryReports(Request $request)
    {
        try {
            // 🔹 Language
            $localeHeader = $request->header('Accept-Language');
            $locale = $localeHeader ? substr($localeHeader, 0, 2) : 'en';

            $language = Languages::where('code', $locale)->first();

            if (!$language) {
                $language = Languages::where('code', 'en')->first();
            }

            $languageId = $language->id;

            $categorySlug = $request->header('X-Category-Slug');

            $search = $request->header('search');
            $limit = 100;

            $categoryId = null;

            if (!empty($categorySlug)) {
                $category = DB::table('categories')
                    ->where('slug', $categorySlug)
                    ->first();

                if ($category) {
                    $categoryId = $category->id;
                }
            }

            // 🔥 MAIN QUERY
            $query = DB::table('reports as r')
                ->join('reports_info as ri', function ($join) use ($languageId) {
                    $join->on('r.report_id', '=', 'ri.report_id')
                        ->where('ri.language_id', $languageId);
                })
                ->leftJoin('category_translations as ct', function ($join) use ($languageId) {
                    $join->on('r.category_id', '=', 'ct.category_id')
                        ->where('ct.language_id', $languageId);
                })
                ->select(
                    'r.report_id as id',
                    'r.pages',
                    'r.forecast_cagr',
                    'r.report_url',
                    'r.category_id',
                    'ct.name as category_name',
                    'ri.keyword',
                    'ri.meta_desc',
                    'ri.updated_at',
                    'ri.thumbnail',
                    'r.base_year',
                    'r.forecast_year'
                )
                ->where('ri.is_deleted', 0)
                ->where('ri.is_publish', 1);

            // 🔥 APPLY CATEGORY FILTER
            if (!empty($categoryId)) {
                $query->where('r.category_id', $categoryId);
            }
            if (!empty($search)) {
                $query->where('ri.keyword', 'LIKE', "%$search%");
            }
            $query->orderBy('r.created_at', 'desc');
            $reports = $query->limit($limit)->get();

            $categoryDetails = null;

            if (!empty($categoryId)) {
                $categoryDetails = DB::table('categories as c')
                    ->join('category_translations as ct', function ($join) use ($languageId) {
                        $join->on('c.id', '=', 'ct.category_id')
                            ->where('ct.language_id', $languageId);
                    })
                    ->where('c.id', $categoryId)
                    ->whereNull('c.deleted_at')
                    ->select(
                        'c.id',
                        'c.slug',
                        'c.thumbnail',
                        'c.parent_id',
                        'ct.name as category_name',
                        'ct.meta_title',
                        'ct.meta_description',
                        'ct.description'
                    )
                    ->first();
            }

            return response()->json([
                'status' => true,
                'reports' => $reports,
                'category' => $categoryDetails,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $err->getMessage()
            ], 500);
        }
    }
    public function getSingleReport(Request $request)
    {
        try {
            // 🔹 Language detect
            $localeHeader = $request->header('Accept-Language');
            $locale = $localeHeader ? substr($localeHeader, 0, 2) : 'en';

            $language = DB::table('languages')->where('code', $locale)->first();

            if (!$language) {
                $language = DB::table('languages')->where('is_default', 1)->first();
            }

            $languageId = $language->id;

            // 🔹 Get slug
            $slug = $request->header('X-Report-Slug');

            if (empty($slug)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Report slug is required'
                ], 400);
            }

            // Check redirect table first
            $redirect = DB::table('report_redirect')->where('redirect_from', $slug)->first();
            if ($redirect) {
                return response()->json([
                    'status'       => true,
                    'redirect'     => true,
                    'redirect_url' => $redirect->redirect_to,
                ], 301);
            }

            // 🔥 SINGLE MAIN QUERY (JOIN ALL TABLES)
            $report = DB::table('reports as r')
                ->join('reports_info as ri', function ($join) use ($languageId) {
                    $join->on('r.report_id', '=', 'ri.report_id')
                        ->where('ri.language_id', $languageId)
                        ->where('ri.is_deleted', 0)
                        ->where('ri.is_publish', 1);
                })
                ->leftJoin('report_descriptions as rd', 'ri.id', '=', 'rd.info_id')
                ->leftJoin('report_prices as rp', 'r.report_id', '=', 'rp.report_id')
                ->leftJoin('categories as c', 'r.category_id', '=', 'c.id')
                ->leftJoin('category_translations as ct', function ($join) use ($languageId) {
                    $join->on('c.id', '=', 'ct.category_id')
                        ->where('ct.language_id', $languageId);
                })
                ->where('r.report_url', $slug)
                ->select(
                    // 🔹 reports
                    'r.*',

                    // 🔹 reports_info
                    'ri.id as info_id',
                    'ri.report_title',
                    'ri.meta_desc',
                    'ri.h1_long_title',
                    'ri.keyword',
                    'ri.thumbnail',
                    'ri.key_market_trends',

                    // 🔹 description
                    'rd.description',
                    'rd.segmentation',
                    'rd.primary_interview_insights',

                    // 🔹 pricing
                    'rp.single',
                    'rp.multiuser',
                    'rp.corporate',
                    'rp.excel',

                    // 🔹 category
                    'c.slug as category_slug',
                    'ct.name as category_name'
                )
                ->first();

            if (!$report) {
                return response()->json([
                    'status' => false,
                    'message' => 'Report not found'
                ], 404);
            }

            // 🔥 ALTERNATE URLS (LIGHT QUERY)
            $alternateUrls = DB::table('reports_info as ri')
                ->join('languages as l', 'ri.language_id', '=', 'l.id')
                ->where('ri.report_id', $report->report_id)
                ->where('ri.is_deleted', 0)
                ->where('ri.is_publish', 1)
                ->select('l.code')
                ->get()
                ->mapWithKeys(function ($item) use ($slug) {
                    return [
                        $item->code => url("/{$item->code}/report/{$slug}")
                    ];
                });

            // 🔹 FINAL RESPONSE
            return response()->json([
                'status' => true,
                'report' => $report,
                'alternate_urls' => $alternateUrls
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
