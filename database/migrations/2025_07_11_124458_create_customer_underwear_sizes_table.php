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
        Schema::create('customer_underwear_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('throat')->nullable();
            $table->string('length')->nullable(); // Довжина виробу
            $table->string('chest_volume')->nullable(); // Об’єм грудей
            $table->string('belly_volume')->nullable(); // Об’єм живота
            $table->string('shoulder')->nullable();
            $table->string('sleeve_length')->nullable(); // Довжина рукава
            $table->string('back_width')->nullable(); // Ширина спинки
            $table->string('cuff')->nullable(); // Зап’ястя
            $table->string('fabric')->nullable(); // Тканина
            $table->boolean('embroidery')->nullable(); // Вишивка
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_underwear_sizes');
    }
};
