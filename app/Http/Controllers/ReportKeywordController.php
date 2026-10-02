<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Report;
use App\Models\ReportKeyword;
use App\Services\ReportGenerationService;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportKeywordController extends Controller
{
    // Manually selectable from the dashboard. 'processing' and 'failed' are
    // set by the report-generation job itself, not chosen by an admin.
    const SELECTABLE_STATUSES = ['pending', 'hold', 'completed'];

    public function index()
    {
        // English-only, per "show only english category" — the keyword
        // table stores a single category_id shared across every language
        // variant of the eventual report.
        $categories = Category::getCategoriesWithTranslation(1);

        return view('keywords.dashboard', compact('categories'));
    }

    // DataTables column index -> DB column, for server-side ordering.
    // Index 0 is the checkbox column (not sortable).
    const ORDER_COLUMNS = [
        2 => 'keyword',
        4 => 'report_status',
        5 => 'created_at',
    ];

    public function ajaxList(Request $request)
    {
        $start = $request->start;
        $length = $request->length;
        $search = $request->search['value'] ?? '';
        $status = $request->query('status');

        $query = DB::table('report_keywords as rk')
            ->leftJoin('category_translations as ct', function ($join) {
                $join->on('rk.category_id', '=', 'ct.category_id')->where('ct.language_id', 1);
            })
            ->select('rk.*', 'ct.name as category_name');

        if ($search) {
            $query->where('rk.keyword', 'like', "%$search%");
        }

        if ($status) {
            $query->where('rk.report_status', $status);
        }

        $total = DB::table('report_keywords')->count();
        $filtered = (clone $query)->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = self::ORDER_COLUMNS[$orderColumnIndex] ?? 'rk.created_at';
        if (!str_contains($orderColumn, '.')) {
            $orderColumn = 'rk.' . $orderColumn;
        }

        $data = $query->orderBy($orderColumn, $orderDir)->offset($start)->limit($length)->get();

        $rows = [];

        foreach ($data as $row) {
            $errorBtn = '';

            if (!empty($row->error)) {
                $errorBtn = '
        <button class="btn-custom btn-danger-gradient viewError"
            data-error="' . htmlspecialchars($row->error, ENT_QUOTES, 'UTF-8') . '">
            View Error
        </button>
    ';
            } else {
                $errorBtn = '<span class="text-muted">--</span>';
            }

            $statusBadge = match ($row->report_status) {
                'completed' => '<span class="badge bg-success">Completed</span>',
                'processing' => '<span class="badge bg-warning">Processing</span>',
                'failed' => '<span class="badge bg-danger">Failed</span>',
                'hold' => '<span class="badge bg-info">Hold</span>',
                default => '<span class="badge bg-secondary">Pending</span>',
            };

            $rows[] = [
                '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">',
                $row->id,
                htmlspecialchars($row->keyword, ENT_QUOTES, 'UTF-8'),
                $row->category_name ? htmlspecialchars($row->category_name, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">— (GPT will choose)</span>',
                $statusBadge,
                \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A'),
                $errorBtn,
                '
                <button class="btn-custom btn-primary-gradient editKeyword"
                    data-id="' . $row->id . '"
                    data-keyword="' . htmlspecialchars($row->keyword, ENT_QUOTES, 'UTF-8') . '"
                    data-status="' . $row->report_status . '"
                    data-base_year_market_size="' . htmlspecialchars((string) $row->base_year_market_size, ENT_QUOTES, 'UTF-8') . '"
                    data-forecast_year_market_size="' . htmlspecialchars((string) $row->forecast_year_market_size, ENT_QUOTES, 'UTF-8') . '"
                    data-forecast_cagr="' . htmlspecialchars((string) $row->forecast_cagr, ENT_QUOTES, 'UTF-8') . '"
                    data-segments="' . htmlspecialchars((string) $row->segments, ENT_QUOTES, 'UTF-8') . '"
                    data-companies="' . htmlspecialchars((string) $row->companies, ENT_QUOTES, 'UTF-8') . '"
                    data-category_id="' . (int) $row->category_id . '">
                    Edit
                </button>

                <button class="btn-custom btn-warning-gradient deleteKeyword"
                    data-id="' . $row->id . '">
                    Delete
                </button>
                '
            ];
        }

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $total,
            "recordsFiltered" => $filtered,
            "data" => $rows
        ]);
    }

    // Bulk delete/status-change for the dashboard's checkbox selection.
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $deleted = ReportKeyword::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => true, 'deleted' => $deleted]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'report_status' => 'required|in:' . implode(',', self::SELECTABLE_STATUSES),
        ]);

        $updated = ReportKeyword::whereIn('id', $request->ids)->update([
            'report_status' => $request->report_status,
        ]);

        return response()->json(['status' => true, 'updated' => $updated]);
    }

    private function keywordRules($idToIgnore = null): array
    {
        return [
            'keyword' => 'required|string',
            'base_year_market_size' => 'required|string',
            'forecast_year_market_size' => 'required|string',
            'forecast_cagr' => 'required|string',
            'segments' => ['required', 'string', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded) || empty($decoded)) {
                    $fail('Segments must be valid, non-empty JSON — e.g. {"By Type": ["A", "B"]}');
                }
            }],
            'companies' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id',
        ];
    }

    // POST /keywords/store — manual "Add Keyword". quick_generate=1 runs
    // generation synchronously right here (not queued) and responds once the
    // report is actually saved; otherwise the keyword is just queued pending
    // for the report:generate cron, same as before.
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->keywordRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // If this keyword is already queued/generated, replace it with a
        // fresh row (status reset to pending, error cleared) instead of
        // blocking as a duplicate — this is how an admin re-queues a market
        // for regeneration by re-adding its keyword. The generate job itself
        // finds the existing report by keyword text and updates it in place,
        // so the underlying report is never duplicated either.
        ReportKeyword::whereRaw('LOWER(keyword) = ?', [mb_strtolower($request->keyword)])->delete();

        $keyword = ReportKeyword::create([
            'keyword' => $request->keyword,
            'base_year_market_size' => $request->base_year_market_size,
            'forecast_year_market_size' => $request->forecast_year_market_size,
            'forecast_cagr' => $request->forecast_cagr,
            'segments' => $request->segments,
            'companies' => $request->companies,
            'category_id' => $request->category_id ?: null,
            'report_status' => 'pending',
        ]);

        if (!$request->boolean('quick_generate')) {
            return response()->json(['status' => true, 'message' => 'Keyword added — queued for the next generate run.']);
        }

        // Quick Generate — run the exact same generation logic as the cron,
        // just synchronously, right now, in this request.
        $keyword->update(['report_status' => 'processing', 'error' => null]);

        try {
            $generated = app(ReportGenerationService::class)->generate($keyword);

            $englishLanguageId = DB::table('languages')->where('code', 'en')->value('id')
                ?? DB::table('languages')->where('is_default', 1)->value('id')
                ?? 1;

            $existingReportId = DB::table('reports_info')
                ->where('keyword', $keyword->keyword)
                ->where('language_id', $englishLanguageId)
                ->where('is_deleted', 0)
                ->value('report_id');

            $saveResult = app(ReportService::class)->saveReport([
                ...$generated,
                'slug' => $keyword->keyword . ' market',
                'language_id' => $englishLanguageId,
                ...get_report_default_prices(),
            ], $existingReportId);

            if (!$saveResult) {
                throw new \Exception("{$keyword->keyword} report could not be saved — check the report form fields.");
            }

            if ($existingReportId) {
                Report::resetTranslationsForUpdate($existingReportId, $englishLanguageId);
            }

            $keyword->update(['is_report_generated' => true, 'report_status' => 'completed']);

            return response()->json(['status' => true, 'message' => "Report for \"{$keyword->keyword}\" generated successfully."]);
        } catch (\Exception $e) {
            $keyword->update(['report_status' => 'failed', 'error' => $e->getMessage()]);

            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge($this->keywordRules($id), [
                'keyword' => 'required|unique:report_keywords,keyword,' . $id,
                'report_status' => 'nullable|in:' . implode(',', self::SELECTABLE_STATUSES),
            ])
        );
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Manually setting the status here is also how a keyword stuck on
        // "failed" (or "processing") after a generation error gets moved
        // back to pending, or held, without it silently reverting.
        ReportKeyword::findOrFail($id)->update([
            'keyword' => $request->keyword,
            'base_year_market_size' => $request->base_year_market_size,
            'forecast_year_market_size' => $request->forecast_year_market_size,
            'forecast_cagr' => $request->forecast_cagr,
            'segments' => $request->segments,
            'companies' => $request->companies,
            'category_id' => $request->category_id ?: null,
            'report_status' => $request->report_status ?: 'pending',
        ]);

        return response()->json(['status' => true]);
    }

    public function delete(Request $request)
    {
        ReportKeyword::findOrFail($request->id)->delete();

        return response()->json(['status' => true]);
    }

    // GET /keywords/download-template
    public function downloadTemplate()
    {
        $csv = "Market Name,Base Year Market Size,Forecast Year Market Size,Forecast CAGR,Segments (JSON),Key Companies (comma separated)\n"
            . 'Example Market,"$1.5 Billion","$3.2 Billion",8.5,"{""By Type"": [""Agricultural"", ""Industrial""]}","Company A, Company B, Company C"' . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="keywords-template.csv"',
        ]);
    }

    // POST /keywords/import-csv — bulk-add keywords with their market data.
    // One category (optional) applies to every row in the batch — selected
    // once on the upload form, not a CSV column.
    //
    // skip_existing = true  -> keywords already in the table are left
    //                          completely untouched; only genuinely new
    //                          ones are inserted.
    // skip_existing = false -> keywords already in the table are deleted
    //                          and re-inserted fresh (status resets to
    //                          pending, error cleared).
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $skipExisting = $request->boolean('skip_existing', true);
        $categoryId = $request->category_id ?: null;

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (!$handle) {
            return response()->json([
                'status' => false,
                'message' => 'Could not read the uploaded file',
            ], 422);
        }

        // Collect + de-duplicate rows from the file itself first.
        $rows = [];
        $invalidCount = 0;
        $isFirstRow = true;

        while (($row = fgetcsv($handle)) !== false) {
            $keyword = trim($row[0] ?? '');

            if ($keyword === '') {
                continue;
            }

            // Skip a header row.
            if ($isFirstRow) {
                $isFirstRow = false;
                if (strcasecmp($keyword, 'Market Name') === 0 || strcasecmp($keyword, 'keyword') === 0) {
                    continue;
                }
            }

            $baseYearMarketSize = trim($row[1] ?? '');
            $forecastYearMarketSize = trim($row[2] ?? '');
            $forecastCagr = trim($row[3] ?? '');
            $segments = trim($row[4] ?? '');
            $companies = trim($row[5] ?? '');

            $decodedSegments = json_decode($segments, true);
            $segmentsValid = json_last_error() === JSON_ERROR_NONE && is_array($decodedSegments) && !empty($decodedSegments);

            if (empty($baseYearMarketSize) || empty($forecastYearMarketSize) || empty($forecastCagr) || !$segmentsValid || empty($companies)) {
                $invalidCount++;
                continue;
            }

            $key = mb_strtolower($keyword);
            $rows[$key] = [
                'keyword' => $keyword,
                'base_year_market_size' => $baseYearMarketSize,
                'forecast_year_market_size' => $forecastYearMarketSize,
                'forecast_cagr' => $forecastCagr,
                'segments' => $segments,
                'companies' => $companies,
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json([
                'status' => false,
                'message' => $invalidCount
                    ? "No valid rows found — {$invalidCount} row(s) were missing required columns or had invalid Segments JSON."
                    : 'No keywords found in the uploaded file',
            ], 422);
        }

        $existing = ReportKeyword::whereIn(
            DB::raw('LOWER(keyword)'),
            array_keys($rows)
        )->get()->keyBy(fn($row) => mb_strtolower($row->keyword));

        $inserted = 0;
        $skipped = 0;
        $replaced = 0;

        foreach ($rows as $lower => $rowData) {
            $existingRow = $existing->get($lower);

            if ($existingRow) {
                if ($skipExisting) {
                    $skipped++;
                    continue;
                }

                $existingRow->delete();
                $replaced++;
            }

            ReportKeyword::create([
                ...$rowData,
                'category_id' => $categoryId,
                'report_status' => 'pending',
            ]);
            $inserted++;
        }

        return response()->json([
            'status' => true,
            'message' => "Imported {$inserted} keyword(s)" .
                ($replaced ? ", replaced {$replaced} existing" : '') .
                ($skipped ? ", skipped {$skipped} existing" : '') .
                ($invalidCount ? ", skipped {$invalidCount} invalid row(s)" : '') . '.',
            'inserted' => $inserted,
            'replaced' => $replaced,
            'skipped' => $skipped,
            'invalid' => $invalidCount,
        ]);
    }
}
