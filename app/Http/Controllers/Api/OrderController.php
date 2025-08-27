<?php
// Fichier: app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Crée une nouvelle commande pour un vendeur spécifique.
     */
    public function placeOrder(Request $request)
    {
        $client = $request->user();
        
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        if ($client->id == $validated['seller_id']) {
            return response()->json(['message' => 'Vous ne pouvez pas commander vos propres produits.'], 400);
        }
        
        $totalAmount = 0;
        $orderItemsData = [];
        
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            if ($product->user_id != $validated['seller_id']) {
                return response()->json(['message' => "Le produit '{$product->name}' n'appartient pas à ce vendeur."], 400);
            }

            $totalAmount += $product->price * $item['quantity'];
            
            // --- LA CORRECTION EST ICI ---
            // On ajoute le seller_id à chaque item de la commande.
            $orderItemsData[] = [
                'product_id' => $product->id,
                'seller_id' => $validated['seller_id'], // On spécifie à quel vendeur appartient cet item
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ];
        }

        try {
            $order = DB::transaction(function () use ($client, $validated, $totalAmount, $orderItemsData) {
                $order = Order::create([
                    'user_id' => $client->id,
                    'seller_id' => $validated['seller_id'],
                    'total_amount' => $totalAmount,
                ]);
                $order->items()->createMany($orderItemsData);
                return $order;
            });
    
            // TODO: Envoyer des notifications par e-mail
    
            return response()->json(['message' => 'Commande passée avec succès !', 'order' => $order], 201);

        } catch (\Exception $e) {
            // Loguer l'erreur pour le débogage
            \Illuminate\Support\Facades\Log::error('Erreur lors de la création de la commande: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la création de la commande.'], 500);
        }
    }

    public function placedOrders(Request $request)
    {
        $orders = $request->user()
            ->ordersPlaced()
            // On charge le vendeur, y compris sa devise
            ->with('seller:id,name,company_name,currency', 'items.product:id,name')
            ->latest()->get();
            
        return response()->json($orders);
    }

    public function receivedOrders(Request $request)
    {
        $orders = $request->user()
            ->ordersReceived()
            // On charge le client, y compris sa devise
            ->with('client:id,name,currency', 'items.product:id,name')
            ->latest()->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        // On vérifie que l'utilisateur connecté est soit le client, soit le vendeur de la commande.
        if ($request->user()->id !== $order->user_id && $request->user()->id !== $order->seller_id) {
            abort(403, 'Action non autorisée.');
        }

        // On charge toutes les relations nécessaires pour l'affichage
        $order->load([
            'client:id,name,email,phone',
            'seller:id,name,company_name,email,phone',
            'items.product:id,name,image_path'
        ]);

        return response()->json($order);
    }
}