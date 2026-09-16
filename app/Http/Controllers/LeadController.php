<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
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
}
