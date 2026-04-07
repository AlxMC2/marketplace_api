<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Rutas públicas — sin autenticación
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Rutas protegidas — requieren token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::get('/seller/products', [ProductController::class, 'myProducts']);
});