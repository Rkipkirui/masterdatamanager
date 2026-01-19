<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change charset and collation to utf8mb4
        DB::statement('ALTER TABLE customer_groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        // Revert to default latin1 if needed
        DB::statement('ALTER TABLE customer_groups CONVERT TO CHARACTER SET latin1 COLLATE latin1_swedish_ci');
    }
};
