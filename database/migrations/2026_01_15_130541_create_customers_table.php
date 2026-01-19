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

            // Basic Info
            $table->string('code')->unique();
            $table->string('name');
            $table->string('foreign_name')->nullable();
            $table->string('group')->nullable();
            $table->string('currency')->default('KES');
            $table->string('pin')->nullable();

            // Contact Info
            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Business Info
            $table->string('shipping_type')->nullable();
            $table->string('dealer_category')->nullable();
            $table->string('type_of_business')->nullable(); 
            $table->boolean('active')->default(true);

            // Account Info
            $table->decimal('account_balance', 15, 2)->default(0);
            $table->decimal('orders', 15, 2)->default(0);
            $table->decimal('deliveries', 15, 2)->default(0);

            // Approvals
            $table->string('it_bl_approval')->default('Not Approved');
            $table->string('gcm_approval')->default('Not Approved');
            $table->string('credit_control_approval')->default('Not Approved');

            // Optional: JSON fields
            $table->json('trading_locations')->nullable();    // e.g., Nairobi, Mombasa…
            $table->json('properties')->nullable();           // Custom properties / tabs
            $table->json('attachments')->nullable();          // File paths or metadata

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
