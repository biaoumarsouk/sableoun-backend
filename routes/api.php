<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\AfficheController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\MessageController; 
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\OrderController;
/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/

// Auth & Password Reset
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [ForgotPasswordController::class, 'forgot']);
Route::post('reset-password', [ForgotPasswordController::class, 'reset'])->name('password.reset');

// Contenus Publics
Route::get('/sellers', [SellerController::class, 'index']);
Route::get('/sellers/{slug}', [SellerController::class, 'showBySlug']);
Route::get('/sellers/{user}/products', [SellerController::class, 'products']);
Route::get('/sellers/{user}/affiches', [SellerController::class, 'affiches']);
Route::get('/affiches/public', [AfficheController::class, 'publicIndex']);
Route::get('/affiches/{affiche}', [AfficheController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'publicShow']);
Route::post('/messages', [MessageController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Routes Protégées (pour les vendeurs connectés)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // --- CRUD Produits (Correct) ---
    Route::get('/user/products', [ProductController::class, 'userProducts']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/edit/{product}', [ProductController::class, 'showForEditing']);
    Route::post('/products/update/{product}', [ProductController::class, 'update']);
    Route::delete('/products/delete/{product}', [ProductController::class, 'destroy']);
    
    // --- CRUD Affiches (Simplifié) ---
    Route::get('/user/affiches', [AfficheController::class, 'userAffiches']); // READ (Lister ses affiches)
    Route::post('/affiches', [AfficheController::class, 'store']); // CREATE
    Route::get('/affiches/edit/{affiche}', [AfficheController::class, 'showForEditing']); // READ (Pour édition)
    Route::post('/affiches/update/{affiche}', [AfficheController::class, 'update']); // UPDATE
    Route::delete('/affiches/delete/{affiche}', [AfficheController::class, 'destroy']); // DELETE
    
    // --- Messagerie (Correct) ---
    Route::get('/user/messages', [MessageController::class, 'index']);
    Route::get('/user/messages/{message}', [MessageController::class, 'show']);
    
    // ---- Modifier profil

    Route::post('/user/profile', [AuthController::class, 'updateProfile']);

    Route::get('/plans', [SubscriptionController::class, 'plans']);
    // Créer un lien de paiement pour s'abonner
    Route::post('/checkout', [SubscriptionController::class, 'createCheckoutLink']);

    Route::post('/order', [OrderController::class, 'placeOrder']);
    Route::get('/orders/placed', [OrderController::class, 'placedOrders']);
    Route::get('/orders/received', [OrderController::class, 'receivedOrders']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});