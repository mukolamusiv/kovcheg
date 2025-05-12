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
        Schema::table('invoice_production_items', function (Blueprint $table) {
            $table->foreignId('production_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('warehouse_to_id')->nullable()->constrained('warehouses')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['warehouse_to_id']);
            $table->dropColumn('warehouse_to_id');
        });
        Schema::table('invoice_production_items', function (Blueprint $table) {
            $table->dropForeign(['production_id']);
            $table->dropColumn('production_id');
        });
    }
};
