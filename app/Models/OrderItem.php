<?php
// Fichier: app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'quantity',
        'price',
    ];

    // Un article de commande appartient à une commande
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Un article de commande est lié à un produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}