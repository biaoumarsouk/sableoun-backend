<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Dans le nouveau fichier de migration ...add_status_to_products_table.php

    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // On ajoute une colonne 'status' après 'price'
            // Elle peut contenir 'published' ou 'draft' (brouillon)
            // Par défaut, un nouveau produit sera un brouillon.
            $table->string('status')->after('price')->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
