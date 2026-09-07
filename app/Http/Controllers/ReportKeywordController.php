<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportKeyword;

class ReportKeywordController extends Controller
{
    // Manually selectable from the dashboard. 'processing' and 'failed' are
    // set by the report-generation job itself, not chosen by an admin.
    const SELECTABLE_STATUSES = ['pending', 'hold', 'completed'];

    public function index()
    {
        return view('keywords.dashboard');
    }

    public function ajaxList(Request $request)
    {
        $start = $request->start;
        $length = $request->length;
        $search = $request->search['value'] ?? '';

        $query = ReportKeyword::query();

        if ($search) {
            $query->where('keyword', 'like', "%$search%");
        }

        $total = ReportKeyword::count();
        $filtered = $query->count();

        $data = $query->offset($start)->limit($length)->latest()->get();

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
                $row->id,
                $row->keyword,
                $statusBadge,
                $row->created_at->format('Y-m-d H:i'),
                $errorBtn,
                '
                <button class="btn-custom btn-primary-gradient editKeyword"
                    data-id="' . $row->id . '"
                    data-keyword="' . htmlspecialchars($row->keyword, ENT_QUOTES, 'UTF-8') . '"
                    data-status="' . $row->report_status . '">
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

    public function store(Request $request)
    {
        $request->validate([
            'keyword' => 'required|unique:report_keywords,keyword'
        ]);

        ReportKeyword::create([
            'keyword' => $request->keyword,
            'report_status' => 'pending'
        ]);

        return response()->json(['status' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'keyword' => 'required|unique:report_keywords,keyword,' . $id,
            'report_status' => 'nullable|in:' . implode(',', self::SELECTABLE_STATUSES),
        ]);

        // Manually setting the status here is the point of this form — a
        // keyword stuck on 'failed' (or 'processing') after a generation
        // error needs a way to be moved back to 'pending' (or held) without
        // it silently reverting on every save.
        ReportKeyword::findOrFail($id)->update([
            'keyword' => $request->keyword,
            'report_status' => $request->report_status ?: 'pending',
        ]);

        return response()->json(['status' => true]);
    }

    public function delete(Request $request)
    {
        ReportKeyword::findOrFail($request->id)->delete();

        return response()->json(['status' => true]);
    }

    // GET /keywords/download-template — a starter CSV: one column, one
    // header row, so the bulk-upload format is unambiguous.
    public function downloadTemplate()
    {
        $csv = "Market Name\n" . "Example Market\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="keywords-template.csv"',
        ]);
    }

    // POST /keywords/import-csv — bulk-add keywords from a CSV whose first
    // column is the market name (a header row, if present, is detected and
    // skipped automatically).
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
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $skipExisting = $request->boolean('skip_existing', true);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (!$handle) {
            return response()->json([
                'status' => false,
                'message' => 'Could not read the uploaded file',
            ], 422);
        }

        // Collect + de-duplicate keywords from the file itself first.
        $keywords = [];
        $isFirstRow = true;

        while (($row = fgetcsv($handle)) !== false) {
            $value = trim($row[0] ?? '');

            if ($value === '') {
                continue;
            }

            // Skip a header row like "Market Name" / "keyword".
            if ($isFirstRow) {
                $isFirstRow = false;
                if (strcasecmp($value, 'Market Name') === 0 || strcasecmp($value, 'keyword') === 0) {
                    continue;
                }
            }

            $key = mb_strtolower($value);
            $keywords[$key] = $value; // last occurrence wins for casing
        }
        fclose($handle);

        if (empty($keywords)) {
            return response()->json([
                'status' => false,
                'message' => 'No keywords found in the uploaded file',
            ], 422);
        }

        $existing = ReportKeyword::whereIn(
            \Illuminate\Support\Facades\DB::raw('LOWER(keyword)'),
            array_keys($keywords)
        )->get()->keyBy(fn($row) => mb_strtolower($row->keyword));

        $inserted = 0;
        $skipped = 0;
        $replaced = 0;

        foreach ($keywords as $lower => $original) {
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
                'keyword' => $original,
                'report_status' => 'pending',
            ]);
            $inserted++;
        }

        return response()->json([
            'status' => true,
            'message' => "Imported {$inserted} keyword(s)" .
                ($replaced ? ", replaced {$replaced} existing" : '') .
                ($skipped ? ", skipped {$skipped} existing" : '') . '.',
            'inserted' => $inserted,
            'replaced' => $replaced,
            'skipped' => $skipped,
        ]);
    }
}
