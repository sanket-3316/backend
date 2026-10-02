<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromptController extends Controller
{
    const TYPES = ['report', 'blog', 'press_release'];

    public function index()
    {
        $prompts = Prompt::orderByDesc('id')->get();

        return view('prompts.dashboard', compact('prompts'));
    }

    private function rules($id = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', self::TYPES),
            'system_prompt' => 'required|string',
            'user_prompt' => 'required|string',
            'temperature' => 'required|numeric|min:0|max:2',
            'max_tokens' => 'required|integer|min:100|max:32000',
            'response_format' => 'required|in:text,json_object',
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        Prompt::create([
            'name' => $request->name,
            'type' => $request->type,
            'system_prompt' => $request->system_prompt,
            'user_prompt' => $request->user_prompt,
            'temperature' => $request->temperature,
            'max_tokens' => $request->max_tokens,
            'response_format' => $request->response_format,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['status' => true, 'message' => 'Prompt added successfully']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules($id));
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        Prompt::findOrFail($id)->update([
            'name' => $request->name,
            'type' => $request->type,
            'system_prompt' => $request->system_prompt,
            'user_prompt' => $request->user_prompt,
            'temperature' => $request->temperature,
            'max_tokens' => $request->max_tokens,
            'response_format' => $request->response_format,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['status' => true, 'message' => 'Prompt updated successfully']);
    }

    public function toggleActive(Request $request, $id)
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->update(['is_active' => !$prompt->is_active]);

        return response()->json(['status' => true, 'is_active' => $prompt->is_active]);
    }

    public function delete(Request $request)
    {
        Prompt::findOrFail($request->id)->delete();

        return response()->json(['status' => true]);
    }
}
