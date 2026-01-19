<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payment_terms', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_payment_terms', 'payment_term_id')) {
                $table->foreignId('payment_term_id')
                      ->after('customer_id')
                      ->constrained('payment_terms')
                      ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_payment_terms', function (Blueprint $table) {
            if (Schema::hasColumn('customer_payment_terms', 'payment_term_id')) {
                $table->dropForeign(['payment_term_id']);
                $table->dropColumn('payment_term_id');
            }
        });
    }
};
