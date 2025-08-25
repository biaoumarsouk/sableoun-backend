<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Dans le nouveau fichier de migration des messages

    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade'); // Le vendeur qui reçoit
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null'); // Le produit concerné (optionnel)
            
            $table->string('sender_name'); // Nom de l'expéditeur
            $table->string('sender_email'); // Email de l'expéditeur
            $table->text('message'); // Le contenu du message
            
            $table->boolean('is_read')->default(false); // Pour savoir si le vendeur l'a lu
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
