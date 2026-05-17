<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
Route::get('/migrate-db', function () {
    Artisan::call('migrate', ["--force" => true]);
    return "Base de données mise à jour avec succès !";
});
Route::get('/', function () {
    return view('welcome');
});
