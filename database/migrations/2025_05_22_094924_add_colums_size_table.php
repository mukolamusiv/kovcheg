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
        Schema::table('production_sizes', function (Blueprint $table) {
            // Existing columns
            // $table->string('throat')->nullable(); // Горловина
            // $table->string('redistribution')->nullable(); // Переділ
            // $table->string('behind')->nullable(); // Зад
            // $table->string('hips')->nullable(); // Стегна
            // $table->string('length')->nullable(); // Довжина
            // $table->string('sleeve')->nullable(); // Рукав
            // $table->string('shoulder')->nullable(); // Плече


            // New columns based on comments
            $table->integer('neck')->nullable(); // Шия
            $table->integer('front')->nullable(); // Перід
            $table->integer('epitrachelion')->nullable(); // Епитрахиля
            $table->integer('abdomen_volume')->nullable(); // Обєм живота
            $table->integer('height')->nullable(); // Ріст
            $table->integer('floor_height')->nullable(); // Ріст до підлоги
            $table->integer('chest_volume')->nullable(); // Обєм грудей
            $table->boolean('cuffs')->nullable(); // Нарукавники
            $table->json('awards')->nullable(); // Нагороди
            $table->boolean('sticharion')->nullable(); // Стихар

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_sizes', function (Blueprint $table) {
            $table->dropColumn([
            'neck', // Шия
            'front', // Перід
            'epitrachelion', // Епитрахиля
            'abdomen_volume', // Обєм живота
            'height', // Ріст
            'floor_height', // Ріст до підлоги
            'chest_volume', // Обєм грудей
            'cuffs', // Нарукавники
            'awards', // Нагороди
            'sticharion', // Стихар
            ]);
        });
    }
};
