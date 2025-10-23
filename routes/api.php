<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Api\V1\CompteController;

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

// Route::prefix('v1')->group(function () {
//     Route::middleware('auth:api')->group(function () {
//         Route::get('/comptes', [CompteController::class, 'index']);
//     });
// });

Route::get('v1/comptes', [CompteController::class, 'index']);
