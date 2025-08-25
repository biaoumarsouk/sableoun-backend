<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Dans ...add_status_to_affiches_table.php

    public function up(): void
    {
        Schema::table('affiches', function (Blueprint $table) {
            // On ajoute la colonne après 'description'
            // Par défaut, une nouvelle affiche est un brouillon ('draft')
            $table->string('status')->after('description')->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiches', function (Blueprint $table) {
            //
        });
    }
};
