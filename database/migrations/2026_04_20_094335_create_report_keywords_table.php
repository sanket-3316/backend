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
        Schema::create('report_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->unique();

            // Report
            $table->boolean('is_report_generated')->default(false);
            $table->string('report_status')->default('pending');
            // pending, processing, completed, failed , hold

            // Future use (blog, PR)
            $table->boolean('is_blog_generated')->default(false);
            $table->string('blog_status')->nullable();

            $table->boolean('is_pr_generated')->default(false);
            $table->string('pr_status')->nullable();

            $table->text('error')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_keywords');
    }
};
