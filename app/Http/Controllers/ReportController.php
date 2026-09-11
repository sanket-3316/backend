<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Languages;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ReportKeyword;
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
            $isTranslation = $request->filled('translation_of');

            $rules = [
                'report_title' => 'required',
                'language_id' => 'required|numeric',

                'meta_desc' => 'required',
                'h1_long_title' => 'required',
                'keyword' => 'required',

                'description' => 'required',
                'segmentation_json' => 'required',
            ];

            if ($isTranslation) {
                // Adding a translation to an EXISTING report — report-level
                // fields (slug, years, pricing, category…) are shared and
                // inherited from the parent report, not re-entered here.
                $rules['translation_of'] = 'required|exists:reports,report_id';
            } else {
                $rules['slug'] = 'required|unique:reports,report_url';
                $rules['base_year'] = 'required';
                $rules['historic_year'] = 'required';
                $rules['forecast_year'] = 'required';
                $rules['single'] = 'required|numeric';
                $rules['multiuser'] = 'required|numeric';
                $rules['corporate'] = 'required|numeric';
                $rules['excel'] = 'required|numeric';
            }

            $request->validate($rules);

            // Upload image
            $imageName = null;
            if ($request->hasFile('thumbnail')) {
                $imageName = time() . '.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('assets/reports/images'), $imageName);
            }

            $data = $request->all();
            $data['thumbnail'] = $imageName;
            $data['segmentation'] = $request->segmentation_json;

            if ($isTranslation) {
                $result = Report::addTranslation($request->translation_of, $data);

                if ($result) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Translation added successfully'
                    ]);
                }
                return response()->json([
                    'status' => false,
                    'message' => 'This report already has a translation in that language'
                ]);
            }

            $result = Report::createFullReport($data);

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

    // ?language_id= selects which translation of the report to load; omit it
    // to fall back to whichever variant the query finds first.
    public function edit(Request $request, $id)
    {
        $data = Report::getReportById($id, $request->query('language_id'));
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        try {

            // ✅ VALIDATION (same as store but slug ignore current)
            $request->validate([
                'report_title' => 'required',
                'slug' => 'required|unique:reports,report_url,' . $id . ',report_id',
                'language_id' => 'required|numeric',

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
                // keep old image — same language variant being edited
                $existing = Report::getReportById($id, $data['language_id']);
                $data['thumbnail'] = $existing->thumbnail ?? null;
            }

            // ✅ UPDATE CALL — updates this specific report_id + language_id variant
            $result = $this->reportService->saveReport($data, $id);


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

    // Dashboard "Generate" button: same GPT prompt sequence as GenerateReportJob
    // (the report:generate cron), but run synchronously here so the response can
    // populate the Add Report modal for review before the admin saves it via the
    // normal /report/store or /report/update flow. This does NOT touch the cron
    // job, the report:generate command, or its schedule.
    public function generateFromKeyword(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
        ]);

        $rawKeyword = trim($request->keyword);

        // Add the keyword if it's new; reuse the existing row otherwise (unique on `keyword`)
        $keywordModel = ReportKeyword::firstOrCreate(
            ['keyword' => $rawKeyword],
            ['report_status' => 'pending']
        );
        $keywordModel->update(['report_status' => 'processing', 'error' => null]);

        try {
            $gptKeyword = $rawKeyword . ' market';

            // Computed once, fed into the market-size prompt and used directly
            // for base_year/forecast_year below — keeps GPT's estimate and the
            // years shown in the modal consistent (mirrors GenerateReportJob).
            $years = report_years();

            $segmentsPrompt = get_report_segmentation_prompt();
            $segmentsResult = search_from_gpt(
                str_replace(['[[keyword]]'], [$gptKeyword], $segmentsPrompt['user']),
                $segmentsPrompt['system'],
                0.08,
                12500,
                'json_object'
            );
            if (empty($segmentsResult)) {
                throw new \Exception('GPT did not return segments');
            }
            $segmentsResult = json_decode($segmentsResult, true);
            if (!isset($segmentsResult['segments'])) {
                throw new \Exception('Invalid segments JSON');
            }

            $keyPlayersPrompt = get_report_key_players_prompt();
            $keyPlayersResult = search_from_gpt(
                str_replace(['[[keyword]]'], [$gptKeyword], $keyPlayersPrompt['user']),
                $keyPlayersPrompt['system'],
                0.08,
                12500,
                'json_object'
            );
            if (empty($keyPlayersResult)) {
                throw new \Exception('GPT did not return key players');
            }
            $keyPlayersResult = json_decode($keyPlayersResult, true);

            $marketSizePrompt = get_report_market_size_data_prompt($years);
            $marketSizeResult = search_from_gpt(
                str_replace(['[[keyword]]'], [$gptKeyword], $marketSizePrompt['user']),
                $marketSizePrompt['system'],
                0.08,
                12500,
                'json_object'
            );
            if (empty($marketSizeResult)) {
                throw new \Exception('GPT did not return market size data');
            }
            $marketSizeResult = json_decode($marketSizeResult, true);

            $descriptionPrompt = get_report_description_prompt(
                $rawKeyword,
                $segmentsResult,
                [
                    'base_year' => $years['base_year'],
                    'forecast_year' => $years['forecast_end_year'],
                    'base_year_market_size' => $marketSizeResult['base_year_market_size'] ?? '',
                    'forecast_market_size' => $marketSizeResult['forecast_market_size'] ?? '',
                    'cagr_percent' => $marketSizeResult['cagr_percent'] ?? '',
                ],
                $keyPlayersResult
            );
            $description = search_from_gpt(
                $descriptionPrompt['user'],
                $descriptionPrompt['system'],
                0.8,
                8000,
                'text'
            );
            $description = clean_gpt_html_response($description);
            if (empty($description)) {
                throw new \Exception('GPT did not return report description');
            }

            $keywordModel->update([
                'is_report_generated' => true,
                'report_status' => 'completed',
            ]);

            return response()->json([
                'status' => true,
                'data' => array_merge([
                    'keyword' => $rawKeyword,
                    'report_title' => generate_report_title($rawKeyword),
                    'h1_long_title' => $segmentsResult['h1_long_title'] ?? '',
                    'meta_desc' => $marketSizeResult['meta_description'] ?? '',

                    'base_year' => $years['base_year'],
                    'historic_year' => $years['historic_start_year'],
                    'forecast_year' => $years['forecast_end_year'],
                    'base_year_market_size' => $marketSizeResult['base_year_market_size'] ?? null,
                    'forecast_year_market_size' => $marketSizeResult['forecast_market_size'] ?? null,
                    'forecast_cagr' => $marketSizeResult['cagr_percent'] ?? null,

                    'key_companys' => $keyPlayersResult['key_players'] ?? [],
                    'segmentation' => $segmentsResult['segments'] ?? [],
                    'description' => $description,

                    // sensible defaults GPT doesn't produce
                    'pages' => report_random_stats()['pages'],
                    'views' => report_random_stats()['views'],
                    'rating' => report_random_stats()['rating'],
                ], get_report_default_prices()),
            ]);
        } catch (\Exception $e) {
            $keywordModel->update([
                'report_status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReports()
    {
        $data = Report::getAllReports();

        return response()->json(['data' => $data]);
    }
    public function getReportLanguages($id)
    {
        $data = DB::table('reports_info as ri')
            ->join('languages as l', 'l.id', '=', 'ri.language_id')
            ->where('ri.report_id', $id)
            ->where('ri.is_deleted', 0)
            ->select('ri.id as info_id', 'ri.report_id', 'ri.language_id', 'ri.report_title', 'l.code', 'l.name as language_name')
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

            // Pagination is opt-in via ?page= — callers that don't paginate
            // (search-as-you-type, etc.) keep the old flat limit-100 behavior.
            $page = $request->query('page');
            $perPage = max(1, (int) $request->query('per_page', 20));

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
                // Match either the bare keyword ("Biogas") or the full report
                // title ("Biogas Market Research Report 2033") — searching the
                // market name as shown on the site was matching neither before.
                $query->where(function ($q) use ($search) {
                    $q->where('ri.keyword', 'LIKE', "%$search%")
                        ->orWhere('ri.report_title', 'LIKE', "%$search%");
                });
            }
            $query->orderBy('r.created_at', 'desc');

            $pagination = null;

            if ($page) {
                $page = max(1, (int) $page);
                $total = (clone $query)->count();

                $reports = $query->forPage($page, $perPage)->get();

                $pagination = [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                ];
            } else {
                $reports = $query->limit(100)->get();
            }

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
                'pagination' => $pagination,
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

            // 🔹 RUNTIME CHART IMAGES (market overview + per-segment) INTO DESCRIPTION
            if (!empty($report->description)) {
                $report->description = inject_report_charts_into_description(
                    $report->description,
                    $report->segmentation,
                    $report->report_url
                );
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

            // 🔹 LANGUAGE-WISE TABLE OF CONTENTS
            $toc = build_report_toc(
                $language->code,
                $report->keyword,
                $report->segmentation,
                $report->historic_year,
                $report->forecast_year,
                $report->key_companys
            );
            $report->toc = $toc;
            // 🔹 FINAL RESPONSE
            return response()->json([
                'status' => true,
                'report' => $report,
                'toc' => $toc,
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

    // GET /api/sitemap-reports?lang=en&page=1&per_page=10000
    //
    // Deliberately separate from categoryReports(): the sitemap only needs
    // report_url + updated_at for every published report, and doesn't want
    // the category joins/lookup that endpoint does. Ordered by report_id
    // (not created_at) so a given page number is a stable, deterministic
    // slice even as new reports are added between requests — required for
    // paginated sitemaps to not duplicate or skip URLs across chunks.
    public function sitemapReports(Request $request)
    {
        try {
            $locale = $request->query('lang')
                ?: ($request->header('Accept-Language') ? substr($request->header('Accept-Language'), 0, 2) : 'en');

            $language = Languages::where('code', $locale)->first()
                ?: Languages::where('is_default', 1)->first();

            $page = max(1, (int) $request->query('page', 1));
            $perPage = max(1, min(10000, (int) $request->query('per_page', 10000)));

            $query = DB::table('reports as r')
                ->join('reports_info as ri', function ($join) use ($language) {
                    $join->on('r.report_id', '=', 'ri.report_id')
                        ->where('ri.language_id', $language->id)
                        ->where('ri.is_deleted', 0)
                        ->where('ri.is_publish', 1);
                })
                ->orderBy('r.report_id')
                ->select('r.report_url', 'ri.updated_at');

            $total = (clone $query)->count();
            $reports = $query->forPage($page, $perPage)->get();

            return response()->json([
                'status' => true,
                'reports' => $reports,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                ],
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $err->getMessage(),
            ], 500);
        }
    }
}
