<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\ReportRedirect;

class RedirectController extends Controller
{
    public function index()
    {
        return view('redirect.dashboard');
    }

    public function ajaxList(Request $request)
    {
        $start  = $request->start;
        $length = $request->length;
        $search = $request->search['value'] ?? '';

        $query = ReportRedirect::with(['createdBy', 'updatedBy']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('redirect_from', 'like', "%$search%")
                  ->orWhere('redirect_to', 'like', "%$search%");
            });
        }

        $total    = ReportRedirect::count();
        $filtered = $query->count();

        $data = $query->latest()->offset($start)->limit($length)->get();

        $rows = [];

        foreach ($data as $row) {
            $rows[] = [
                $row->id,
                htmlspecialchars($row->redirect_from, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row->redirect_to, ENT_QUOTES, 'UTF-8'),
                $row->createdBy->name ?? '—',
                $row->updatedBy->name ?? '—',
                $row->created_at->format('Y-m-d H:i'),
                $row->updated_at->format('Y-m-d H:i'),
                '
                <button class="btn-custom btn-primary-gradient editRedirect"
                    data-id="' . $row->id . '"
                    data-from="' . htmlspecialchars($row->redirect_from, ENT_QUOTES, 'UTF-8') . '"
                    data-to="' . htmlspecialchars($row->redirect_to, ENT_QUOTES, 'UTF-8') . '">
                    Edit
                </button>
                <button class="btn-custom btn-warning-gradient deleteRedirect"
                    data-id="' . $row->id . '">
                    Delete
                </button>
                ',
            ];
        }

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $existingFroms = DB::table('report_redirect')->pluck('redirect_from')->toArray();
        $existingTos   = DB::table('report_redirect')->pluck('redirect_to')->toArray();

        $request->validate([
            'redirect_from' => [
                'required', 'string', 'max:500',
                'unique:report_redirect,redirect_from',
                Rule::notIn($existingTos),
            ],
            'redirect_to' => [
                'required', 'string', 'max:500',
                Rule::notIn($existingFroms),
            ],
        ], [
            'redirect_from.not_in' => 'This URL already exists as a redirect destination.',
            'redirect_to.not_in'   => 'This URL already exists as a redirect source.',
        ]);

        ReportRedirect::create([
            'redirect_from' => $request->redirect_from,
            'redirect_to'   => $request->redirect_to,
            'created_by'    => Auth::id(),
            'updated_by'    => Auth::id(),
        ]);

        return response()->json(['status' => true]);
    }

    public function update(Request $request, $id)
    {
        $existingFroms = DB::table('report_redirect')->where('id', '!=', $id)->pluck('redirect_from')->toArray();
        $existingTos   = DB::table('report_redirect')->where('id', '!=', $id)->pluck('redirect_to')->toArray();

        $request->validate([
            'redirect_from' => [
                'required', 'string', 'max:500',
                Rule::unique('report_redirect', 'redirect_from')->ignore($id),
                Rule::notIn($existingTos),
            ],
            'redirect_to' => [
                'required', 'string', 'max:500',
                Rule::notIn($existingFroms),
            ],
        ], [
            'redirect_from.not_in' => 'This URL already exists as a redirect destination.',
            'redirect_to.not_in'   => 'This URL already exists as a redirect source.',
        ]);

        ReportRedirect::findOrFail($id)->update([
            'redirect_from' => $request->redirect_from,
            'redirect_to'   => $request->redirect_to,
            'updated_by'    => Auth::id(),
        ]);

        return response()->json(['status' => true]);
    }

    public function delete(Request $request)
    {
        ReportRedirect::findOrFail($request->id)->delete();

        return response()->json(['status' => true]);
    }
}
