<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // ==== Relations ====
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->string('series_id')->nullable(); // series as string for flexibility
            $table->foreignId('group_id')->nullable()->constrained('customer_groups');
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists');
            $table->foreignId('account_payable_id')->nullable()->constrained('account_payables');
            $table->foreignId('dealer_category_id')->nullable()->constrained('dealer_categories');
            $table->foreignId('dealer_type_id')->nullable()->constrained('dealer_types');
            $table->foreignId('territory_id')->nullable()->constrained('territories');

            // ==== Basic Info ====
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('pin')->nullable();

            // ==== Contact Info ====
            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('id_staff_no_2')->nullable();
            $table->string('address_id')->nullable();
            $table->string('po_box')->nullable();
            $table->string('city')->nullable();

            // ==== Business / Finance Info ====
            $table->decimal('dealer_discount', 5, 2)->default(0);

            // Optional JSON fields
            $table->json('properties')->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
