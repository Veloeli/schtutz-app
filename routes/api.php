<?php

use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\ItemApiController;
use App\Http\Controllers\Api\CategoryApiController;

Route::middleware('auth:sanctum')->group(function () {

    // User
    Route::get('/user', [UserApiController::class, 'show']);

    // Items
    Route::get('/items', [ItemApiController::class, 'index']);
    Route::post('/items', [ItemApiController::class, 'store']);
    Route::put('/items/{item}', [ItemApiController::class, 'update']);

    // Categories
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::post('/categories', [CategoryApiController::class, 'store']);
    Route::post('/categories/{category}/share', [CategoryApiController::class, 'share']);
    Route::post('/categories/{category}/subscribe', [CategoryApiController::class, 'subscribe']);
});
