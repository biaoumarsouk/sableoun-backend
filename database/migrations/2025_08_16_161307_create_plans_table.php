<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Premium Mensuel"
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->string('interval'); // "month" ou "year"
            $table->json('features')->nullable();
            $table->string('stripe_price_id')->nullable(); // L'ID du prix sur Stripe
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
