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
        // Постачальники
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            // $table->decimal('debet',10,2)->default(0); //дебит
            // $table->decimal('credit',10,2)->default(0); //кредит
            $table->timestamps();
            $table->softDeletes();
        });

        // банківські реквізити постачальників
        Schema::create('supplier_bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('bank_swift')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_card_number')->nullable();
            $table->string('bank_card_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_bank_details');
        Schema::dropIfExists('suppliers');
    }
};
