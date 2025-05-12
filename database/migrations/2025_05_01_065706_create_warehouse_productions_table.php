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

        // Create the warehouse_productions table
        // This table will store the relationship between warehouses and productions
        // It will include the quantity and price of each production in the warehouse
        // The table will have a foreign key constraint to the warehouses and productions tables
        // The foreign key constraint will ensure that if a warehouse or production is deleted,
        Schema::create('warehouse_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->float('price')->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Add a foreign key constraint to the productions table
        // This will link the template_productions_id in the productions table to the id in the template_productions table
        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('template_productions_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('invoice_production_items', function (Blueprint $table) {
            $table->foreignId('warehouse_productions_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

         // 1. Видаляємо зовнішній ключ з таблиці invoice_production_items
        Schema::table('invoice_production_items', function (Blueprint $table) {
            $table->dropForeign(['warehouse_productions_id']); // або уточни назву поля
            $table->dropColumn('warehouse_productions_id');
        });
        // Drop the foreign key constraint from the productions table
        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['template_productions_id']);
            $table->dropColumn('template_productions_id');
        });
        Schema::dropIfExists('warehouse_productions');
    }
};
