<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_keywords', function (Blueprint $table) {
            // User-provided market data — the generate cron no longer asks
            // GPT to invent these, it passes them straight into the single
            // combined report prompt (see prompts table / PromptService).
            $table->string('base_year_market_size')->nullable()->after('keyword');
            $table->string('forecast_year_market_size')->nullable()->after('base_year_market_size');
            $table->string('forecast_cagr')->nullable()->after('forecast_year_market_size');
            $table->longText('segments')->nullable()->after('forecast_cagr'); // raw JSON text as typed/uploaded
            $table->text('companies')->nullable()->after('segments'); // comma-separated

            // Optional — chosen by the admin (manual add) or applied to a
            // whole CSV batch (import). NULL means "ask GPT to suggest one"
            // at generation time. Stored as an id only, never a name.
            $table->unsignedBigInteger('category_id')->nullable()->after('companies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_keywords', function (Blueprint $table) {
            $table->dropColumn([
                'base_year_market_size',
                'forecast_year_market_size',
                'forecast_cagr',
                'segments',
                'companies',
                'category_id',
            ]);
        });
    }
};
