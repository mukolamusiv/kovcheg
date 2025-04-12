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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('status', ['створено', 'в роботі','виготовлено', 'скасовано'])->default('створено');
            $table->enum('type', ['замовлення', 'на продаж'])->default('замовлення');
            $table->enum('pay', ['не оплачено','завдататок', 'оплачено','часткова оплата'])->default('не оплачено');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            //$table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->date('production_date')->nullable(); // Дата виготовлення
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        //етапи виробництва
        Schema::create('production_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('status', ['очікує','в роботі','виготовлено', 'скасовано'])->default('очікує');
            $table->decimal('paid_worker', 15, 2); // Оплата парацівника
            $table->date('date')->nullable(); // Дата виготовлення
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        //матеріали виробництва
        Schema::create('production_materials', function (Blueprint $table) {
            $table->id();
           // $table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('production_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(1); // кількість
            $table->decimal('price', 10, 2)->default(0);    // ціна за одиницю
            $table->string('description')->nullable();
            $table->date('date_writing_off')->nullable(); // Дата списання
            $table->foreignId('warehouse_id')->nullable()->constrained()->cascadeOnDelete(); // Склад
            $table->timestamps();
            $table->softDeletes();
        });

         //Списання матеріалів через накладну для виробництва
            Schema::create('invoice_production_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->foreignId('production_id')->constrained()->cascadeOnDelete();
                //$table->foreignId('material_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 15, 2)->default(1); // Кількість
                $table->decimal('price', 15, 2)->default(0);    // Ціна
                $table->decimal('total', 15, 2)->default(0);    // Сума
                $table->timestamps();
                $table->softDeletes();
            });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_production_items');
        Schema::dropIfExists('production_materials');
        Schema::dropIfExists('production_stages');
        Schema::dropIfExists('productions');
    }
};
