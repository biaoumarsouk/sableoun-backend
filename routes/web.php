<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. ROUTE DE MIGRATION (À visiter une seule fois pour créer vos tables)
Route::get('/migrate-db', function () {
    try {
        // Force la migration en production
        Artisan::call('migrate', ["--force" => true]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Base de données mise à jour avec succès !',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur lors de la migration : ' . $e->getMessage()
        ], 500);
    }
});

// 2. PAGE D'ACCUEIL
Route::get('/', function () {
    return view('welcome');
});

// 3. OPTIONNEL : VIDER LE CACHE (Utile si vous changez des variables .env)
Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return "Cache vidé !";
});
