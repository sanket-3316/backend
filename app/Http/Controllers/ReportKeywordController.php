<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportKeyword;

class ReportKeywordController extends Controller
{
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
                    data-keyword="' . $row->keyword . '">
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
            'keyword' => 'required|unique:report_keywords,keyword,' . $id
        ]);

        ReportKeyword::findOrFail($id)->update([
            'keyword' => $request->keyword,
            'report_status' => 'pending' // reset if changed
        ]);

        return response()->json(['status' => true]);
    }

    public function delete(Request $request)
    {
        ReportKeyword::findOrFail($request->id)->delete();

        return response()->json(['status' => true]);
    }
}
