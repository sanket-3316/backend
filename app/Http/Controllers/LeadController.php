<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    // ============================================================================
    //  PUBLIC — lead capture form on the live site (unauthenticated)
    // ============================================================================

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:32',
            'designation' => 'nullable|string|max:255',
            'custom_requirements' => 'nullable|string',
            'reportId' => 'nullable|integer',
            'categoryId' => 'nullable|integer',
            'locale' => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $status = LeadStatus::firstOrCreate(['name' => 'New']);

            // The requested locale, if provided by the caller, is recorded
            // against the same `languages` table every other locale-aware
            // endpoint uses — falls back to the site default when missing
            // or unrecognized rather than leaving it blank.
            $language = $request->locale
                ? Languages::where('code', $request->locale)->first()
                : null;
            if (!$language) {
                $language = Languages::where('is_default', 1)->first();
            }

            Lead::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'designation' => $request->designation,
                'message' => $request->custom_requirements,
                'report_id' => $request->reportId ?: null,
                'category_id' => $request->categoryId ?: null,
                'language_id' => $language?->id,
                'status_id' => $status->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Request submitted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================================
    //  ADMIN — leads dashboard (authenticated)
    // ============================================================================

    public function index()
    {
        $reports = DB::table('reports_info')
            ->where('language_id', 1)
            ->where('is_deleted', 0)
            ->orderBy('report_title')
            ->select('report_id', 'report_title')
            ->get();

        $categories = Category::getCategoriesWithTranslation(1);
        $statuses = LeadStatus::orderBy('id')->get();

        return view('leads.dashboard', compact('reports', 'categories', 'statuses'));
    }

    // Shared select/join used by both the datatable list and the CSV export,
    // so the two never drift out of sync on what a "lead row" looks like.
    private function baseQuery()
    {
        return DB::table('leads as l')
            ->leftJoin('reports_info as ri', function ($join) {
                $join->on('l.report_id', '=', 'ri.report_id')->where('ri.language_id', 1);
            })
            ->leftJoin('category_translations as ct', function ($join) {
                $join->on('l.category_id', '=', 'ct.category_id')->where('ct.language_id', 1);
            })
            ->leftJoin('lead_statuses as ls', 'l.status_id', '=', 'ls.id')
            ->where('l.is_deleted', 0)
            ->select(
                'l.id',
                'l.name',
                'l.email',
                'l.phone',
                'l.designation',
                'l.message',
                'l.report_id',
                'l.category_id',
                'l.status_id',
                'l.created_at',
                'ri.report_title',
                'ct.name as category_name',
                'ls.name as status_name'
            );
    }

    // GET /leads/list — full non-deleted lead set; date-range and report-name
    // filters are applied client-side (same pattern the report dashboard
    // uses), so this always returns everything and lets the table redraw
    // instantly as filters change without a round-trip per filter change.
    public function ajaxList()
    {
        $data = $this->baseQuery()->orderByDesc('l.created_at')->get();

        return response()->json(['data' => $data]);
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:32',
            'designation' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'report_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'status_id' => 'nullable|integer|exists:lead_statuses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $status = $request->status_id ?: LeadStatus::firstOrCreate(['name' => 'New'])->id;
        $language = Languages::where('is_default', 1)->first();

        Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'message' => $request->message,
            'report_id' => $request->report_id ?: null,
            'category_id' => $request->category_id ?: null,
            'language_id' => $language?->id,
            'status_id' => $status,
        ]);

        return response()->json(['status' => true, 'message' => 'Lead added successfully']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:32',
            'designation' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'report_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'status_id' => 'nullable|integer|exists:lead_statuses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $lead = Lead::where('is_deleted', 0)->find($id);
        if (!$lead) {
            return response()->json(['status' => false, 'message' => 'Lead not found'], 404);
        }

        // report_id/category_id are only overwritten when the request
        // actually includes the key (an empty value still clears it) — the
        // dashboard form always resends its <select> values, but a caller
        // that omits the field entirely shouldn't silently wipe it.
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'message' => $request->message,
            'status_id' => $request->status_id ?: $lead->status_id,
        ];

        if ($request->has('report_id')) {
            $data['report_id'] = $request->report_id ?: null;
        }
        if ($request->has('category_id')) {
            $data['category_id'] = $request->category_id ?: null;
        }

        $lead->update($data);

        return response()->json(['status' => true, 'message' => 'Lead updated successfully']);
    }

    // Soft delete — same is_deleted=1 convention used elsewhere in this app
    // (reports_info, users), not Eloquent's SoftDeletes/deleted_at.
    public function delete(Request $request)
    {
        $lead = Lead::find($request->id);
        if (!$lead) {
            return response()->json(['status' => false, 'message' => 'Lead not found'], 404);
        }

        $lead->update(['is_deleted' => 1]);

        return response()->json(['status' => true, 'message' => 'Lead deleted successfully']);
    }

    // GET /leads/export — Excel-compatible CSV (no spreadsheet library in
    // this project; a .csv opens directly in Excel, same approach already
    // used for the keyword bulk-upload template download).
    //
    // ?ids=1,2,3        -> export exactly those leads (checkbox selection),
    //                      ignores any date filter.
    // ?date_from&?date_to -> otherwise export everything in that range
    //                        (either/both optional).
    public function exportCsv(Request $request)
    {
        $query = $this->baseQuery();

        $ids = array_filter(array_map('trim', explode(',', (string) $request->query('ids'))));

        if (!empty($ids)) {
            $query->whereIn('l.id', $ids);
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('l.created_at', '>=', $request->query('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('l.created_at', '<=', $request->query('date_to'));
            }
        }

        $leads = $query->orderByDesc('l.created_at')->get();

        $filename = 'leads-export-' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Designation', 'Report', 'Category', 'Status', 'Message', 'Submitted At']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->designation,
                    $lead->report_title,
                    $lead->category_name,
                    $lead->status_name,
                    $lead->message,
                    $lead->created_at,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
