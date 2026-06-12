<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports_info', function (Blueprint $table) {
            $table->id();

            // correct way (no constrained())
            $table->unsignedBigInteger('report_id');
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();

            $table->string('report_title', 2000);
            $table->string('meta_desc', 2000);
            $table->string('h1_long_title', 7000);
            $table->string('keyword');

            $table->text('key_market_trends')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('unique_id');

            $table->boolean('is_internal_link')->default(0);
            $table->string('internal_link_reports')->nullable();

            $table->boolean('is_publish')->default(1);
            $table->boolean('is_deleted')->default(0);

            $table->timestamps();

            $table->unique(['keyword', 'language_id']);

            // correct FK
            $table->foreign('report_id')
                ->references('report_id')
                ->on('reports')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports_info');
    }
};