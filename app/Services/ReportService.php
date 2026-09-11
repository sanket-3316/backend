<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReportService
{
    // Pass $reportId to UPDATE an existing report (a specific language variant
    // of it — $data['language_id'] selects which one); omit it to CREATE a
    // brand-new report.
    public function saveReport(array $data, $reportId = null)
    {
        try {

            // 🔹 Step 1: Validate Required Fields
            $validator = Validator::make($data, [
                'report_title' => 'required|string',
                'slug' => 'required|string',
                'category_id' => 'required|numeric',
                'language_id' => 'required|numeric',

                'base_year' => 'required',
                'forecast_year' => 'required',

                'base_year_market_size' => 'required|numeric',
                'forecast_year_market_size' => 'required|numeric',
                'forecast_cagr' => 'required|numeric',

                'key_companys' => 'required',

                'meta_desc' => 'required',
                'h1_long_title' => 'required',
                'keyword' => 'required',

                'description' => 'required',
                'segmentation_json' => 'required',

                'single' => 'required|numeric',
                'multiuser' => 'required|numeric',
                'corporate' => 'required|numeric',
                'excel' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            // 🔹 Step 2: Normalize Data
            $data['segmentation'] = $data['segmentation_json'];

            $data['author'] = $data['author'] ?? 1;
            $data['pages'] = $data['pages'] ?? rand(120, 250);
            $data['views'] = 0;
            $data['rating'] = 4.5;
            $data['format'] = 'PDF';

            // 🔹 Step 3: Clean JSON (important)
            $data['key_companys'] = is_array($data['key_companys'])
                ? json_encode($data['key_companys'])
                : $data['key_companys'];

            $data['segmentation'] = is_array($data['segmentation'])
                ? json_encode($data['segmentation'])
                : $data['segmentation'];

            // 🔹 Step 4: Slug Safety
            $data['slug'] = Str::slug($data['slug']);

            // 🔹 Step 5: Update the existing report, or insert a brand-new one
            return $reportId
                ? Report::updateFullReport($reportId, $data)
                : Report::createFullReport($data);
        } catch (\Exception $e) {

            Log::error("ReportService Error: " . $e->getMessage());
            return false;
        }
    }
}
