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
        Schema::create('customer_dalmatic_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('throat')->nullable(); // Горловина
            $table->string('length')->nullable(); // Довжина виробу
            $table->string('width')->nullable(); // Ширина виробу
            $table->string('sleeve_type')->nullable(); // Тип рукава
            $table->string('sleeve_length')->nullable(); // Довжина рукава
            $table->string('shoulder')->nullable(); // Плече
            $table->boolean('stand_collar_zip')->nullable(); // Стійка замок
            $table->string('fabric')->nullable(); // Тканина
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_dalmatic_sizes');
    }
};
