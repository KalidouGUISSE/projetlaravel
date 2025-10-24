<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes API version 1
Route::prefix('v1')->group(function () {

    // Route pour tester le RequestController (pas d'auth)
    Route::get('/request', [RequestController::class, 'handle']);

    // Route de login pour Sanctum
    Route::post('/login', function (Request $request) {
        // Exemple simple de login, à adapter
        return response()->json(['message' => 'Login endpoint'], 200);
    });

    // Routes pour les clients (avec auth et rate limiting)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::apiResource('clients', ClientController::class);
    });

    // Routes pour les comptes (avec auth et rate limiting)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
         Route::get('/comptes', [CompteController::class, 'index']);
    });
    // Routes pour les comptes (avec auth et rate limiting)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
         Route::get('/comptes', [CompteController::class, 'index']);
         Route::post('/comptes', [CompteController::class, 'store']);
         Route::get('/comptes/{id}', [CompteController::class, 'show']);
    });

    // Route de test pour comptes sans auth (pour tester)
    Route::get('/comptes-test', [CompteController::class, 'index']);

    // Routes pour les admins (avec auth et rate limiting)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::apiResource('admins', AdminController::class)->except(['index', 'show', 'update', 'destroy']);
        Route::get('/admins/comptes', [AdminController::class, 'getAllComptes']);
    });

});
