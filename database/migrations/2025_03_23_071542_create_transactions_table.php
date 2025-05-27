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
        Schema::create('transactions', function (Blueprint $table) {
            // $table->id();
            // $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            // $table->decimal('amount', 15, 2);
            // $table->enum('type', ['дебет', 'кредит']);
            // $table->text('notes')->nullable();
            // $table->date('transaction_date');
            // $table->enum('status', ['створено', 'проведено', 'скасовано'])->default('створено');
            // $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('supplier_id')->nullable()->constrained();
            // $table->foreignId('customer_id')->nullable()->constrained();
            // $table->timestamps();
            // $table->softDeletes();

            $table->id();
            $table->string('reference_number')->unique(); //номер транзакції
            $table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete(); //номер накладної
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->enum('status', ['створено', 'проведено', 'скасовано'])->default('створено');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); //користувач що створив транзакцію
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transaction_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->enum('entry_type', ['дебет', 'кредит']);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_entries');
        Schema::dropIfExists('transactions');
    }
};
