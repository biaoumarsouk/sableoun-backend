<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Fichier: database/migrations/..._create_products_table.php
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Lien vers le vendeur
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 8, 2); // Prix avec 2 décimales
            $table->string('image_path')->nullable(); // Chemin vers l'image du produit
            $table->timestamps(); // Crée les colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
