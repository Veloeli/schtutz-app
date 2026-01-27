<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/**
 * Items
 */
Route::view('/', 'items')->name('items.index');
Route::view('/items', 'items');

/**
 * Categories
 */
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

/**
 * Debug / Diagnostics
 */
Route::get('/db-check', function () {
    return [
        'env'    => env('DB_DATABASE'),
        'config' => config('database.connections.mysql.database'),
        'actual' => DB::connection()->getDatabaseName(),
    ];
});

Route::get('/db-all', function () {
    return config('database.connections');
});
