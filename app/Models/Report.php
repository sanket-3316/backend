<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Report extends Model
{
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'report_url',
        'category_id',
        'base_year_market_size',
        'forecast_year_market_size',
        'forecast_cagr',
        'key_companys',
        'base_year',
        'historic_year',
        'forecast_year',
        'pages',
        'views',
        'rating',
        'author',
        'formats',
        'is_translated_all_lang'
    ];



    // 🔹 INSERT FULL DATA
    public static function createFullReport($data)
    {
        DB::beginTransaction();

        try {
            $reportId = DB::table('reports')->insertGetId([
                'report_url' => $data['slug'],
                'category_id' => $data['category_id'],
                'base_year_market_size' => $data['base_year_market_size'],
                'forecast_year_market_size' => $data['forecast_year_market_size'],
                'forecast_cagr' => $data['forecast_cagr'],
                'base_year' => $data['base_year'],
                'historic_year' => $data['historic_year'],
                'forecast_year' => $data['forecast_year'],
                'author' => $data['author'],
                'pages' => $data['pages'],
                'views' => $data['views'],
                'rating' => $data['rating'],
                'format' => $data['format'],
                'key_companys' => $data['key_companys'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $infoId = DB::table('reports_info')->insertGetId([
                'report_id' => $reportId,
                'language_id' => $data['language_id'] ?? 1,
                'report_title' => $data['report_title'],
                'meta_desc' => $data['meta_desc'],
                'h1_long_title' => $data['h1_long_title'],
                'keyword' => $data['keyword'],
                'thumbnail' => $data['thumbnail'],
                'unique_id' => uniqid(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('report_descriptions')->insert([
                'info_id' => $infoId,
                'description' => $data['description'],
                'segmentation' => $data['segmentation'],
                // 'primary_interview_insights' => $data['primary_interview_insights']
            ]);
            DB::table('report_prices')->insert([
                'report_id' => $reportId,
                'single' => $data['single'],
                'multiuser' => $data['multiuser'],
                'corporate' => $data['corporate'],
                'excel' => $data['excel'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    // 🔹 GET SINGLE REPORT (optionally a specific language variant — a report_id
    // can have multiple reports_info rows, one per language)
    public static function getReportById($id, $languageId = null)
    {
        $query = DB::table('reports as r')
            ->select('r.*', 'ri.*', 'rd.*', 'rp.*')
            ->join('reports_info as ri', 'r.report_id', '=', 'ri.report_id')
            ->join('report_descriptions as rd', 'ri.id', '=', 'rd.info_id')
            ->join('report_prices as rp', 'r.report_id', '=', 'rp.report_id')
            ->where('r.report_id', $id);

        if ($languageId) {
            $query->where('ri.language_id', $languageId);
        }

        return $query->first();
    }

    // 🔹 ADD A TRANSLATION to an EXISTING report — inserts a new reports_info +
    // report_descriptions row for a language this report doesn't have yet.
    // Report-level fields (reports, report_prices) are shared across every
    // language variant and are left untouched.
    public static function addTranslation($reportId, $data)
    {
        DB::beginTransaction();

        try {
            $exists = DB::table('reports_info')
                ->where('report_id', $reportId)
                ->where('language_id', $data['language_id'])
                ->where('is_deleted', 0)
                ->exists();

            if ($exists) {
                DB::rollBack();
                return false;
            }

            $infoId = DB::table('reports_info')->insertGetId([
                'report_id' => $reportId,
                'language_id' => $data['language_id'],
                'report_title' => $data['report_title'],
                'meta_desc' => $data['meta_desc'],
                'h1_long_title' => $data['h1_long_title'],
                'keyword' => $data['keyword'],
                'thumbnail' => $data['thumbnail'],
                'unique_id' => uniqid(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('report_descriptions')->insert([
                'info_id' => $infoId,
                'description' => $data['description'],
                'segmentation' => $data['segmentation'],
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    // 🔹 UPDATE REPORT
    public static function updateFullReport($id, $data)
    {
        DB::beginTransaction();

        try {

            // ===== UPDATE REPORT TABLE =====
            DB::table('reports')->where('report_id', $id)->update([
                'report_url' => $data['slug'],
                'category_id' => $data['category_id'],
                'base_year_market_size' => $data['base_year_market_size'],
                'forecast_year_market_size' => $data['forecast_year_market_size'],
                'forecast_cagr' => $data['forecast_cagr'],
                'base_year' => $data['base_year'],
                'historic_year' => $data['historic_year'],
                'forecast_year' => $data['forecast_year'],
                'author' => $data['author'],
                'pages' => $data['pages'],
                'views' => $data['views'],
                'rating' => $data['rating'],
                'format' => $data['format'],
                'key_companys' => $data['key_companys'],
                'updated_at' => now()
            ]);

            // ===== GET REPORT INFO — the SPECIFIC language variant being edited.
            // Without the language_id filter this would find (and the update
            // below would then silently overwrite) whichever variant happens
            // to sort first, corrupting every other translation of this report.
            $info = DB::table('reports_info')
                ->where('report_id', $id)
                ->where('language_id', $data['language_id'])
                ->first();

            if (!$info) {
                DB::rollBack();
                return false;
            }

            // ===== UPDATE REPORT INFO =====
            DB::table('reports_info')->where('id', $info->id)->update([
                'report_title' => $data['report_title'],
                'meta_desc' => $data['meta_desc'],
                'h1_long_title' => $data['h1_long_title'],
                'keyword' => $data['keyword'],
                'thumbnail' => $data['thumbnail'],
                'updated_at' => now()
            ]);

            // ===== UPDATE DESCRIPTION =====
            DB::table('report_descriptions')->where('info_id', $info->id)->update([
                'description' => $data['description'],
                'segmentation' => $data['segmentation'],
            ]);

            // ===== UPDATE PRICE =====
            DB::table('report_prices')->where('report_id', $id)->update([
                'single' => $data['single'],
                'multiuser' => $data['multiuser'],
                'corporate' => $data['corporate'],
                'excel' => $data['excel'],
                'updated_at' => now()
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    // 🔹 DELETE
    public static function deleteReport($id)
    {
        return DB::table('reports')->where('report_id', $id)->delete();
    }

    // 🔹 GET ALL REPORTS
    public static function getAllReports()
    {
        return DB::table('reports as r')
            ->join('reports_info as ri', 'r.report_id', '=', 'ri.report_id')
            // ->leftJoin('categories as c', 'r.category_id', '=', 'c.id')
            ->leftJoin('category_translations as ct', 'r.category_id', '=', 'ct.category_id')
            ->select(
                'r.report_id',
                'r.report_url',
                'ri.report_title',
                'ri.thumbnail',
                'r.category_id',
                'ct.name as category_name',
                DB::raw('(SELECT COUNT(*) FROM reports_info WHERE report_id = r.report_id) as lang_count')
            )
            ->where('ri.language_id', 1)
            ->where('ct.language_id', 1)
            ->where('ri.is_deleted', 0)
            ->orderBy('r.created_at', 'desc')
            ->get();
    }
}
