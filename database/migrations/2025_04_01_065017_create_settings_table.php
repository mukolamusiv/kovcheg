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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Ідентифікатор користувача, зовнішній ключ
            $table->unsignedBigInteger('user_id');
            // Фіксована націнка, десяткове число з точністю до 10 цифр, 2 з яких після коми
            $table->decimal('markup_fixed', 10, 2)->nullable();
            // Відсоткова націнка, десяткове число з точністю до 5 цифр, 2 з яких після коми
            $table->decimal('markup_percentage', 5, 2)->nullable();
            // Максимальна націнка, десяткове число з точністю до 10 цифр, 2 з яких після коми
            $table->decimal('markup_max', 10, 2)->nullable();
            // Дозвіл на знижки, булеве значення, за замовчуванням false
            $table->boolean('allow_discounts')->default(false);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
