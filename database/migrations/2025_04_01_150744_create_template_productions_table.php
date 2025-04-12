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
        Schema::create('template_productions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_productions_materials', function (Blueprint $table) {
             $table->id();
             $table->foreignId('template_productions_id')->constrained()->cascadeOnDelete();
             $table->foreignId('material_id')->constrained()->cascadeOnDelete();
             //$table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete();
             $table->decimal('quantity', 10, 2)->default(1); // кількість
             //$table->decimal('price', 10, 2)->default(0);    // ціна за одиницю
             $table->string('description')->nullable();
             //$table->date('date_writing_off')->nullable(); // Дата списання
             $table->foreignId('warehouse_id')->nullable()->constrained()->cascadeOnDelete(); // Склад
             $table->timestamps();
             $table->softDeletes();
        });

        Schema::create('template_productions_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_productions_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            //$table->enum('status', ['очікує','в роботі','виготовлено', 'скасовано'])->default('очікує');
            $table->decimal('paid_worker', 15, 2); // Оплата парацівника
           // $table->date('date')->nullable(); // Дата виготовлення
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_productions_stages');
        Schema::dropIfExists('template_productions_materials');
        Schema::dropIfExists('template_productions');
    }
};
