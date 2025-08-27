<?php
// Fichier: app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'status',
        'total_amount',
        'shipping_address',
    ];

    // --- LA RELATION MANQUANTE EST ICI ---
    /**
     * Une commande contient plusieurs articles (order items).
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    // --- FIN DE L'AJOUT ---

    // Une commande appartient à un client (User)
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Une commande est destinée à un vendeur (User)
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}