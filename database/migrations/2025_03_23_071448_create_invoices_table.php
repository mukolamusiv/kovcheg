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
        //Накладні
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->date('invoice_date');
            $table->date('due_date')->nullable(); // Дата оплати
            $table->decimal('total', 15, 2); // Сума
            $table->decimal('paid', 15, 2);  // Оплачено
            $table->decimal('due', 15, 2)->default(0); // Заборгованість
            $table->decimal('discount', 15, 2)->default(0); // Знижка
            $table->decimal('shipping', 15, 2)->default(0); // Доставка
            $table->enum('type', ['постачання', 'переміщення', 'продаж', 'повернення','списання'])->default('списання');
            $table->enum('payment_status', ['оплачено', 'частково оплачено', 'не оплачено'])->default('не оплачено');
            $table->enum('status', ['створено', 'проведено', 'скасовано'])->default('створено');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Позиції накладних
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained();
            $table->foreignId('material_id')->constrained();
            $table->decimal('quantity', 15, 2); // Кількість
            $table->decimal('price', 15, 2);    // Ціна
            $table->decimal('total', 15, 2);    // Сума
            $table->timestamps();
            $table->softDeletes();
        });

        // Позиції накладних виробництва
        // Schema::create('invoice_', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('invoice_id')->constrained();
        //     $table->foreignId('material_id')->constrained();
        //     $table->decimal('quantity', 15, 2); // Кількість
        //     $table->decimal('price', 15, 2);    // Ціна
        //     $table->decimal('total', 15, 2);    // Сума
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
