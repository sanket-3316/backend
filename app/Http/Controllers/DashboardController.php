<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todayReports = DB::table('reports')->whereDate('created_at', now()->toDateString())->count();
        $totalReports = DB::table('reports')->count();
        $totalLeads = DB::table('leads')->count();

        // Latest 10 login events — who logged in, when, and when (if) they logged out.
        $recentLogins = DB::table('login_logs as ll')
            ->join('users', 'users.id', '=', 'll.user_id')
            ->select('ll.id', 'users.name as user_name', 'users.email as user_email', 'll.login_at', 'll.logout_at', 'll.ip_address')
            ->orderByDesc('ll.login_at')
            ->limit(10)
            ->get();

        return view('dashbaord', compact('todayReports', 'totalReports', 'totalLeads', 'recentLogins'));
    }

    // GET /dashboard/report-stats?range=today|yesterday|week|month|year
    // Language-wise count of reports_info rows created in the selected range
    // (covers both freshly generated English reports and translations added
    // by the translate cron — both are real "reports created" events).
    public function reportStats(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request->query('range', 'today'));

        $data = DB::table('reports_info as ri')
            ->join('languages as l', 'l.id', '=', 'ri.language_id')
            ->where('ri.is_deleted', 0)
            ->whereBetween('ri.created_at', [$start, $end])
            ->select('l.name as language', DB::raw('COUNT(*) as count'))
            ->groupBy('l.name')
            ->orderBy('l.name')
            ->get();

        return response()->json(['data' => $data]);
    }

    // GET /dashboard/lead-stats — day-wise lead count for the last 7 days
    // (including today), zero-filled for days with no leads.
    public function leadStats()
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $rows = DB::table('leads')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d M'),
                'count' => (int) ($rows[$date]->count ?? 0),
            ];
        }

        return response()->json(['data' => $data]);
    }

    private function resolveDateRange($range)
    {
        return match ($range) {
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    public function upload_editor_image(Request $request)
    {
        if ($request->hasFile('file')) {

            $file = $request->file('file');

            // store in storage/app/public/uploads
            $path = $file->store('uploads', 'public');

            return response()->json([
                'location' => asset('storage/' . $path)
            ]);
        }

        return response()->json([
            'error' => 'No file uploaded'
        ], 400);
    }
}
