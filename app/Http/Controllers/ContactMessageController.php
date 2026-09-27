<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller
{
    // ============================================================================
    //  PUBLIC — contact-us form on the live site (unauthenticated)
    // ============================================================================

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:32',
            'job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            ContactMessage::create($request->only(['name', 'email', 'phone', 'job_title', 'company', 'message']));

            return response()->json([
                'status' => true,
                'message' => 'Your message has been submitted successfully',
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
    //  ADMIN — contact us dashboard (authenticated)
    // ============================================================================

    public function index()
    {
        return view('contact-messages.dashboard');
    }

    // ?status=active (default) shows is_deleted=0, ?status=inactive shows is_deleted=1
    public function ajaxList(Request $request)
    {
        $status = $request->query('status', 'active');

        $data = ContactMessage::where('is_deleted', $status === 'inactive' ? 1 : 0)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function show($id)
    {
        $message = ContactMessage::find($id);

        if (!$message) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $message]);
    }

    public function delete(Request $request)
    {
        $message = ContactMessage::find($request->id);
        if (!$message) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $message->update(['is_deleted' => 1]);

        return response()->json(['status' => true, 'message' => 'Message deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $deleted = ContactMessage::whereIn('id', $request->ids)->update(['is_deleted' => 1]);

        return response()->json(['status' => true, 'deleted' => $deleted]);
    }

    public function restore(Request $request)
    {
        $message = ContactMessage::find($request->id);
        if (!$message) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $message->update(['is_deleted' => 0]);

        return response()->json(['status' => true, 'message' => 'Message restored successfully']);
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $restored = ContactMessage::whereIn('id', $request->ids)->update(['is_deleted' => 0]);

        return response()->json(['status' => true, 'restored' => $restored]);
    }
}
