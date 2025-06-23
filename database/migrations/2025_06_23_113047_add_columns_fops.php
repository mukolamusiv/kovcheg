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
        Schema::table('fops', function (Blueprint $table) {
            $table->string('ipn')->unique(); // ІПН
        });

          // Додаємо зв’язок з ФОП до накладних
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('fop_id')->nullable()->constrained('fops')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['fop_id']);
            $table->dropColumn('fop_id');
        });

        Schema::table('fops', function (Blueprint $table) {
            $table->dropColumn('ipn');
        });
    }
};
