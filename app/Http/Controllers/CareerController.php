<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareerController extends Controller
{
    public function index()
    {
        $careers = Career::orderBy('sort_order')->orderByDesc('id')->get();
        return view('career.dashboard', compact('careers'));
    }

    public function list()
    {
        $careers = Career::orderBy('sort_order')->orderByDesc('id')->get();

        $data = $careers->map(function ($career) {
            return [
                'id' => $career->id,
                'role' => $career->role,
                'practice_area' => $career->practice_area,
                'location' => $career->location,
                'status' => $career->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Hidden</span>',
                'action' => '
                    <button class="btn-outline-custom btn-outline-primary-gradient btn-floating-sm editCareer" data-id="' . $career->id . '">
                        <i class="fa fa-pen"></i>
                    </button>
                    <button class="btn-outline-custom btn-outline-warning-gradient btn-floating-sm deleteCareer" data-id="' . $career->id . '">
                        <i class="fa fa-trash"></i>
                    </button>
                ',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show($id)
    {
        return response()->json(Career::findOrFail($id));
    }

    private function validated(Request $request)
    {
        return Validator::make($request->all(), [
            'role' => 'required|string|max:255',
            'practice_area' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validated($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        Career::create([
            'role' => $request->role,
            'practice_area' => $request->practice_area,
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Career opening added successfully',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validated($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $career = Career::findOrFail($id);

        $career->update([
            'role' => $request->role,
            'practice_area' => $request->practice_area,
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Career opening updated successfully',
        ]);
    }

    public function delete(Request $request)
    {
        Career::find($request->id)?->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deleted',
        ]);
    }

    // ============================================================
    // **********************  API **********************
    // ============================================================

    public function get_all_careers(Request $request)
    {
        try {
            $careers = Career::where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(['id', 'role', 'practice_area', 'location', 'description']);

            return response()->json([
                'status' => true,
                'careers' => $careers,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $err->getMessage(),
            ], 500);
        }
    }
}
