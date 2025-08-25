<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // --- MÉTHODES PUBLIQUES ---
    public function index() {
        // Ne retourne QUE les produits publiés
        return response()->json(
            Product::with('user')->where('status', 'published')->latest()->get()
        );
    }

    public function publicShow(Product $product) {
        // Si quelqu'un a le lien direct vers un produit non publié, on le bloque
        if ($product->status !== 'published') {
            abort(404, 'Produit non trouvé.'); // On renvoie 404 pour ne pas révéler son existence
        }
        return response()->json($product->load('user'));
    }

    // --- MÉTHODES PROTÉGÉES ---
    public function userProducts(Request $request) {
        return response()->json($request->user()->products()->latest()->get());
    }

    public function showForEditing(Product $product) {
        if (Gate::denies('manage-product', $product)) {
            abort(403, 'Action non autorisée.');
        }
        return response()->json($product);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'description' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:published,draft', // <-- Validation du statut
        ]);
        $validated['image_path'] = $request->file('image')->store('products', 'public');
        $product = $request->user()->products()->create($validated);
        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product) {
        if (Gate::denies('manage-product', $product)) {
            abort(403, 'Action non autorisée.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'description' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:published,draft', // <-- Validation du statut
        ]);
        if ($request->hasFile('image')) {
            if ($product->image_path) { Storage::disk('public')->delete($product->image_path); }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }
        $product->update($validated);
        return response()->json(['message' => 'Produit mis à jour avec succès!', 'product' => $product]);
    }

    public function destroy(Product $product) {
        if (Gate::denies('manage-product', $product)) {
            abort(403, 'Action non autorisée.');
        }
        if ($product->image_path) { Storage::disk('public')->delete($product->image_path); }
        $product->delete();
        return response()->json(['message' => 'Produit supprimé avec succès!']);
    }
}