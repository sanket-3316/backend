<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashbaord');
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
