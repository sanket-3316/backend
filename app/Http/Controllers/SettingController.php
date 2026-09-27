<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    // ============================================================================
    //  OPENAI API KEY
    // ============================================================================

    public function apiKey()
    {
        $key = get_setting('openai_api_key', '');

        // Masked — the admin should be able to confirm a key is set and
        // recognize which one, without the full secret sitting in the
        // page's HTML/response.
        $masked = $key ? substr($key, 0, 7) . str_repeat('•', max(0, strlen($key) - 11)) . substr($key, -4) : null;

        return view('settings.api-key', compact('masked'));
    }

    public function updateApiKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'openai_api_key' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        set_setting('openai_api_key', trim($request->openai_api_key));

        return response()->json(['status' => true, 'message' => 'API key updated successfully']);
    }

    // ============================================================================
    //  CONTACT DETAILS — fed to the public website via /api/contact-details
    // ============================================================================

    public function contactDetails()
    {
        $details = [
            'phone_usa' => get_setting('contact_phone_usa', ''),
            'phone_emea' => get_setting('contact_phone_emea', ''),
            'email' => get_setting('contact_email', ''),
        ];

        return view('settings.contact-details', compact('details'));
    }

    public function updateContactDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_usa' => 'nullable|string|max:50',
            'phone_emea' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        set_setting('contact_phone_usa', $request->phone_usa);
        set_setting('contact_phone_emea', $request->phone_emea);
        set_setting('contact_email', $request->email);

        return response()->json(['status' => true, 'message' => 'Contact details updated successfully']);
    }
}
