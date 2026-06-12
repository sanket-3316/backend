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
        Schema::create('blogs', function (Blueprint $table) {
            $table->bigIncrements('blog_id');

            $table->string('blog_slug')->unique();

            // no foreign key (as per your requirement)
            $table->unsignedBigInteger('author')->nullable();

            // optional relation with reports
            $table->unsignedBigInteger('report_id')->nullable();

            $table->string('thumbnail')->nullable();

            $table->boolean('is_deleted')->default(0);

            $table->timestamps();

            // optional FK to reports (recommended)
            // $table->foreign('report_id')
            //     ->references('report_id')
            //     ->on('reports')
            //     ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
