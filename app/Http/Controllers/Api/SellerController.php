<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * Lister tous les vendeurs (tous les utilisateurs dans ce cas).
     */
    public function index()
    {
        // On récupère TOUS les utilisateurs, en comptant leurs produits.
        $sellers = User::withCount('products')->latest()->get();

        return response()->json($sellers);
    }

    /**
     * Afficher les informations d'un utilisateur/vendeur spécifique.
     */
    public function show(User $user)
    {
        // --- ON SUPPRIME LA VÉRIFICATION DU RÔLE ---
        // if ($user->role !== 'seller') { ... } // Ligne supprimée

        // On renvoie directement l'utilisateur trouvé par le Route Model Binding.
        return response()->json($user);
    }

    /**
     * Lister tous les produits d'un utilisateur/vendeur spécifique.
     */
    // NOUVELLE VERSION CORRIGÉE
    public function products(User $user)
    {
        // On charge la relation 'user' explicitement, même si cela semble redondant.
        // Cela garantit que le format des données est TOUJOURS le même.
        $products = $user->products()->where('status', 'published')->with('user')->latest()->get();
        
        return response()->json($products);
    }

    public function affiches(User $user)
    {
        // On utilise la relation 'affiches'
        $affiches = $user->affiches()->where('status', 'published')->latest()->get(); // <-- CETTE LIGNE PLANTE
        return response()->json($affiches);
    }

    public function showBySlug($slug)
    {
        $seller = User::where('slug', $slug)->firstOrFail();
        return response()->json($seller);
    }
}