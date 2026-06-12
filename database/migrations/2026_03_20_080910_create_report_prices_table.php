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
        Schema::create('report_prices', function (Blueprint $table) {
            $table->id();

            // FIXED FK
            $table->unsignedBigInteger('report_id');

            $table->integer('single')->default(2999);
            $table->integer('multiuser')->default(3999);
            $table->integer('corporate')->default(5999);
            $table->integer('excel')->default(1999);

            $table->timestamps();

            $table->foreign('report_id')
                ->references('report_id')
                ->on('reports')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_prices');
    }
};
