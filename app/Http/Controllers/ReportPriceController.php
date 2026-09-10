<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportPriceController extends Controller
{
    // The editable price columns on the report_prices table.
    private const COLUMNS = ['single', 'multiuser', 'corporate', 'excel'];

    public function index()
    {
        return view('report-price.dashboard', [
            'columns'     => self::COLUMNS,
            'reportCount' => DB::table('report_prices')->count(),
        ]);
    }

    // Sets one price column to a single value for EVERY report.
    public function applyToAll(Request $request)
    {
        $request->validate([
            'column' => ['required', 'string', Rule::in(self::COLUMNS)],
            'price'  => ['required', 'integer', 'min:0'],
        ]);

        $affected = DB::table('report_prices')->update([
            $request->column => (int) $request->price,
            'updated_at'     => now(),
        ]);

        return response()->json([
            'status'   => true,
            'message'  => "Applied {$request->column} price to {$affected} report(s).",
            'affected' => $affected,
        ]);
    }
}
