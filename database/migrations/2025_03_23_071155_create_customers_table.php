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
        // Створення таблиці клієнтів
        Schema::create('customers', function (Blueprint $table) {
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

        //Розміри клієнтів
        Schema::create('customer_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->string('throat')->nullable();//горловина
            $table->string('redistribution')->nullable();//переділ
            $table->string('behind')->nullable();//зад
            $table->string('hips')->nullable();//стегна
            $table->string('length')->nullable();//довжина
            $table->string('sleeve')->nullable();//рукав
            $table->string('shoulder')->nullable();//плече
            $table->string('comment')->nullable();//коментар
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sizes');
        Schema::dropIfExists('customers');
    }
};
