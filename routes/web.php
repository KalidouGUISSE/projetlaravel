<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwaggerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', [SwaggerController::class, 'ui'])->name('swagger.ui');
Route::get('/api/docs.json', [SwaggerController::class, 'json'])->name('swagger.json');

// Routes pour Swagger avec préfixe guisse
Route::prefix('guisse')->group(function () {
    Route::get('/documentation', [SwaggerController::class, 'ui'])->name('swagger.ui.prefixed');
    Route::get('/docs.json', [SwaggerController::class, 'json'])->name('swagger.json.prefixed');
});
