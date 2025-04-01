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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            //додати унікальний код матеріалу штрихкод
            //додати код виробника
            //додати виробника
            //додати колір такнини

//фів

            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('unit', ['метри погонні','одиниці','кг','літри'])->default('метри погонні');
            //$table->decimal('price', 10, 2);    // ціна за одиницю
            //$table->decimal('quantity', 10, 2); // кількість
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

         //залишки на складі
         Schema::create('warehouse_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2); // кількість
            $table->decimal('price', 10, 2);    // ціна за одиницю
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_materials');
        Schema::dropIfExists('materials');
    }
};
