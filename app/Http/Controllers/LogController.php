<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index()
    {
        $users = DB::table('users')->where('is_deleted', 0)->select('id', 'name')->orderBy('name')->get();

        return view('logs.dashboard', compact('users'));
    }

    public function ajaxList(Request $request)
    {
        $query = DB::table('login_logs as ll')
            ->join('users', 'users.id', '=', 'll.user_id')
            ->select('ll.id', 'users.name as user_name', 'users.email as user_email', 'll.login_at', 'll.logout_at', 'll.ip_address', 'll.user_agent')
            ->orderByDesc('ll.login_at');

        if ($request->filled('user_id')) {
            $query->where('ll.user_id', $request->query('user_id'));
        }

        return response()->json(['data' => $query->get()]);
    }
}
