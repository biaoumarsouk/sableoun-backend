<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Product;
use App\Models\Message;
use App\Models\Affiche;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Règle pour les produits
        Gate::define('manage-product', function (User $user, Product $product) {
            return $user->id === $product->user_id;
        });

        // Règle pour les messages
        Gate::define('view-message', function (User $user, Message $message) {
            return $user->id === $message->seller_id;
        });

        // --- RÈGLE POUR LES AFFICHES AVEC DÉBOGAGE ---
        Gate::define('manage-affiche', function (User $user, Affiche $affiche) {

            // --- LIGNES DE DÉBOGAGE ---
            Log::info('Vérification du Gate [manage-affiche]:', [
                'user_id_connecte'   => $user->id,
                'type_user_id'       => gettype($user->id),
                'affiche_owner_id'   => $affiche->user_id,
                'type_affiche_owner' => gettype($affiche->user_id),
                'sont_egaux?'        => ($user->id === $affiche->user_id),
            ]);
            // --- FIN DES LIGNES DE DÉBOGAGE ---

            return $user->id === $affiche->user_id;
        });
    }
}