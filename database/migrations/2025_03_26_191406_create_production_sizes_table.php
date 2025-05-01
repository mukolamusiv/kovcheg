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
        Schema::create('production_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnDelete();
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

            /**
             *
             * Горловина - int
             * Шия - int
             * Перід - int
             * Зад - int
             * Епитрахиля - int
             * Плечі - int
             * Рукав - int
             * Обєм живота - int
             * Ріст - int
             * Ріст до підлоги - int
             * Обєм грудей - int
             * Нарукавники - bool
             * Нагороди - list
             * Стихар - bool
             * Примітки - string
             *
             */

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_sizes');
    }
};
