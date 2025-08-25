<?php
// Fichier: app/Http/Controllers/Api/SubscriptionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;

class SubscriptionController extends Controller
{
    /**
     * Liste les plans d'abonnement disponibles.
     */
    public function plans()
    {
        return response()->json(Plan::all());
    }

    /**
     * Crée un lien de paiement pour la passerelle choisie.
     */
    public function createCheckoutLink(Request $request)
    {
        $user = $request->user();
        
        // On vérifie si l'utilisateur n'est pas déjà abonné pour éviter les doubles paiements
        if ($user->hasActiveSubscription()) {
            return response()->json(['message' => 'Vous avez déjà un abonnement actif.'], 400);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'gateway' => 'required|in:stripe,kkiapay,fedapay',
        ]);
        
        $plan = Plan::find($validated['plan_id']);

        switch ($validated['gateway']) {
            case 'stripe':
                Stripe::setApiKey(env('STRIPE_SECRET'));
                $checkout_session = StripeCheckoutSession::create([
                    'customer_email' => $user->email,
                    'line_items' => [['price' => $plan->stripe_price_id, 'quantity' => 1]],
                    'mode' => 'subscription',
                    'success_url' => env('FRONTEND_URL') . '/dashboard/abonnement?status=success',
                    'cancel_url' => env('FRONTEND_URL') . '/dashboard/abonnement',
                    'metadata' => [ 'user_id' => $user->id, 'plan_id' => $plan->id, ]
                ]);
                return response()->json(['checkout_url' => $checkout_session->url]);

            case 'kkiapay':
                // Logique future pour KkiaPay
                return response()->json(['checkout_url' => '...lien-kkiapay...']);

            case 'fedapay':
                // Logique future pour FedaPay
                return response()->json(['checkout_url' => '...lien-fedapay...']);
        }
    }

    // Plus tard, nous ajouterons un WebhookController pour gérer la confirmation des paiements
}