<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\AdminController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/user', function ($request) {
    return $request->user();
});

Route::apiResource('clients', ClientController::class);

// Routes pour les comptes (Client)
Route::get('v1/comptes', [CompteController::class, 'index']);

// Routes pour les admins
Route::apiResource('admins', AdminController::class)->except(['index', 'show', 'update', 'destroy']);
Route::prefix('admins')->group(function () {
    Route::get('/comptes', [AdminController::class, 'getAllComptes']);
});
