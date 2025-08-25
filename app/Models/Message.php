<?php
// Fichier: app/Models/Message.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'product_id',
        'affiche_id',
        'sender_name',
        'sender_email',
        'message',
        'is_read',
    ];

    // Un message appartient à un vendeur (destinataire)
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Un message peut être lié à un produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function affiche()
    {
        return $this->belongsTo(Affiche::class);
    }
}