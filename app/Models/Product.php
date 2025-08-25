<?php

// Fichier: app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'image_path',
        'status',
    ];

    // On définit la relation : un produit appartient à un utilisateur (vendeur)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}