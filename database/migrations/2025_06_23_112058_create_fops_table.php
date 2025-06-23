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
        Schema::create('fops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Назва ФОП');
            $table->string('email')->nullable()->comment('Email ФОП');
            $table->string('phone')->nullable()->comment('Телефон ФОП');
            $table->string('address')->nullable()->comment('Адреса ФОП');
            $table->text('iban')->nullable()->comment('Рахунок IBAN ФОП');
            $table->string('bank_name')->nullable()->comment('Назва банку ФОП');
            $table->string('bank_code')->nullable()->comment('Код банку ФОП');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fops');
    }
};
