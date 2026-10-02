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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // admin-facing label, e.g. "Default Report Prompt"
            // Which generation cron this prompt is for — only 'report' is used
            // today; 'blog'/'press_release' are reserved for future crons that
            // will reuse this same table, filtering on type.
            $table->enum('type', ['report', 'blog', 'press_release'])->default('report');
            $table->longText('system_prompt');
            $table->longText('user_prompt');
            $table->decimal('temperature', 3, 2)->default(0.4);
            $table->unsignedInteger('max_tokens')->default(14000);
            $table->enum('response_format', ['text', 'json_object'])->default('json_object');
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
