<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            // Add SAP property number
            $table->unsignedTinyInteger('property_no')->nullable()->after('dealer_discount');

            // Change series_id to integer
            $table->unsignedInteger('series_id')->nullable()->change();

            // Remove old JSON properties
            if (Schema::hasColumn('customers', 'properties')) {
                $table->dropColumn('properties');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            // Restore JSON column
            $table->json('properties')->nullable();

            // Revert series_id to string
            $table->string('series_id')->nullable()->change();

            // Remove property_no
            $table->dropColumn('property_no');
        });
    }
};
