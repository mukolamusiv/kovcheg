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
        Schema::create('customer_robe_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('throat')->nullable(); // Горловина
            $table->string('front')->nullable(); // Перід
            $table->string('front_type')->nullable(); // Тип переду
            $table->string('back')->nullable(); // Зад
            $table->string('epitrachelion_length')->nullable(); // Єпитрахиль – довжина
            $table->string('epitrachelion_type')->nullable(); // Тип єпитрахилі
            $table->string('cuff_type')->nullable(); // Тип нарукавника
            $table->boolean('awards')->nullable(); // Нагороди
            $table->string('tape')->nullable(); // Тасьма
            $table->boolean('clasp')->nullable(); // Застібка
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_robe_sizes');
    }
};
