<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sap_users', function (Blueprint $table) {
            $table->id();
            $table->string('sap_user_code')->unique();   // SAP User Code
            $table->string('sap_user_name');            // SAP User Name
            $table->string('email')->nullable();
            $table->string('user_code')->nullable();    // optional field from SAP
            $table->boolean('is_active')->default(true);
            $table->string('password')->nullable();     // local password for login
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sap_users');
    }
};

