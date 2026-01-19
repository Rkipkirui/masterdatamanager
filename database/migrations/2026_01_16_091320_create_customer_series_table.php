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
        Schema::create('customer_series', function (Blueprint $table) {
            $table->id();
            $table->integer('series');        // Series code from SAP (e.g., 960)
            $table->string('series_name');    // SeriesName from SAP (e.g., C-MSA-M)
            $table->integer('next_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_series');
    }
};
