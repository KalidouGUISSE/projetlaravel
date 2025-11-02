<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AuthController;

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

// Routes d'authentification avec préfixe guisse uniquement
Route::prefix('v1/auth')->group(function () {
    // Endpoints séparés pour admin et client
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
    Route::post('/client/login', [AuthController::class, 'clientLogin']);

    // Endpoint générique (pour compatibilité)
    Route::post('/login', [AuthController::class, 'login']);

    // Routes communes
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

// Routes API version 1
Route::prefix('v1')->group(function () {
    
    // Routes publiques pour créer admin et client (sans authentification)
    Route::post('/clients', [ClientController::class, 'store']);
    Route::post('/admins', [AdminController::class, 'store']);
    // Route::get('/admins', [AdminController::class, 'index'])->middleware('role:admin');
    Route::get('/admins', [AdminController::class, 'index']);

    // Route pour tester le RequestController (pas d'auth)
    Route::get('/request', [RequestController::class, 'handle']);

    // Routes protégées nécessitant une authentification
    Route::middleware([\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\LoggingMiddleware::class])->group(function () {

        // Routes pour les clients (nécessitent auth)
        Route::get('/clients', [ClientController::class, 'index'])->middleware('role:admin');
        Route::get('/clients/{id}', [ClientController::class, 'show'])->middleware('role:admin');
        Route::get('/clients/telephone/{telephone}', [ClientController::class, 'getByTelephone'])->middleware('role:admin');
        Route::put('/clients/{id}', [ClientController::class, 'update'])->middleware('role:admin');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->middleware('role:admin');

        // Routes pour les comptes - accès selon le rôle utilisateur
        Route::get('/comptes', [CompteController::class, 'index']);
        Route::post('/comptes', [CompteController::class, 'store']);
        Route::get('/comptes/{id}', [CompteController::class, 'show']);
        Route::put('/comptes/{id}', [CompteController::class, 'update']);
        Route::delete('/comptes/{id}', [CompteController::class, 'destroy']);

        // Routes pour bloquer/débloquer les comptes (admin seulement)
        Route::post('/comptes/{id}/bloquer', [CompteController::class, 'bloquer'])->middleware('role:admin');
        Route::post('/comptes/{id}/debloquer', [CompteController::class, 'debloquer'])->middleware('role:admin');

        // Route pour bloquer plusieurs comptes via job (admin seulement)
        Route::post('/comptes/bloquer-job', [CompteController::class, 'bloquerViaJob'])->middleware('role:admin');

        // Routes pour les admins (admin seulement)
        // Route::get('/admins', [AdminController::class, 'index'])->middleware('role:admin');
        Route::get('/admins/{id}', [AdminController::class, 'show'])->middleware('role:admin');
        Route::put('/admins/{id}', [AdminController::class, 'update'])->middleware('role:admin');
        Route::delete('/admins/{id}', [AdminController::class, 'destroy'])->middleware('role:admin');
        Route::get('/admins/comptes', [AdminController::class, 'getAllComptes'])->middleware('role:admin');
    });

    // Route de test pour comptes sans auth (pour tester)
    Route::get('/comptes-test', [CompteController::class, 'index']);

});
