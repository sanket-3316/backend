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
        Schema::create('blog_descriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('blog_id');
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();

            $table->string('meta_title')->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('meta_keyword')->nullable();

            $table->longText('description')->nullable();

            $table->timestamps();

            // relations
            $table->foreign('blog_id')
                ->references('blog_id')
                ->on('blogs')
                ->cascadeOnDelete();

            // unique language per blog
            $table->unique(['blog_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_descriptions');
    }
};
