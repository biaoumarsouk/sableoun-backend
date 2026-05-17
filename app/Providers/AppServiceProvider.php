<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Notifications\ResetPassword; // <-- AJOUTÉ
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
        // 1. Configuration de l'URL pour le mot de passe oublié (React / Vercel)
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            // Cela redirige l'utilisateur vers votre frontend sur Vercel
            return 'https://sableoun.vercel.app/reset-password?token='.$token.'&email='.$notifiable->getEmailForPasswordReset();
        });

        // 2. Règle pour les produits
        Gate::define('manage-product', function (User $user, Product $product) {
            return $user->id === $product->user_id;
        });

        // 3. Règle pour les messages
        Gate::define('view-message', function (User $user, Message $message) {
            return $user->id === $message->seller_id;
        });

        // 4. RÈGLE POUR LES AFFICHES AVEC DÉBOGAGE
        Gate::define('manage-affiche', function (User $user, Affiche $affiche) {
            Log::info('Vérification du Gate [manage-affiche]:', [
                'user_id_connecte'   => $user->id,
                'type_user_id'       => gettype($user->id),
                'affiche_owner_id'   => $affiche->user_id,
                'type_affiche_owner' => gettype($affiche->user_id),
                'sont_egaux?'        => ($user->id === $affiche->user_id),
            ]);

            return $user->id === $affiche->user_id;
        });
    }
}