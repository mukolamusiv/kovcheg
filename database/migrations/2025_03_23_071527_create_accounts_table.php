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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->enum('account_type', ['актив', 'пасив', 'дохід', 'витрати'])->default('актив');
            $table->enum('account_category', ['готівка', 'банк', 'клієнт', 'постачальник', 'інше'])->default('готівка');
            $table->enum('currency', ['UAH', 'USD', 'EUR'])->default('UAH');
            // Поліморфні зв'язки
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
