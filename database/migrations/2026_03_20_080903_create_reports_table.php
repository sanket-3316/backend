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
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->string('report_url')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('base_year_market_size')->nullable();
            $table->string('forecast_year_market_size')->nullable();
            $table->string('forecast_cagr')->nullable();

            $table->text('key_companys')->nullable();

            $table->string('base_year');
            $table->string('historic_year');
            $table->string('forecast_year');

            $table->integer('pages')->default(250);
            $table->integer('views')->default(246);
            $table->double('rating')->default(4.6);

            $table->foreignId('author')->nullable();

            $table->string('format');
            $table->tinyInteger('is_translated_all_lang')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
