<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::middleware('check.order.owner')->group(function () {
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    });
});
