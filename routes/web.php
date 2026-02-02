<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubscriptionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentItemController;

Route::resource('documents', DocumentController::class);

Route::redirect('/', '/documents');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('documents', DocumentController::class);
    Route::resource('documents.items', DocumentItemController::class);
    
    Route::post('/documents/toggle', [DocumentController::class, 'toggle'])->name('documents.toggle');
});

Route::middleware('auth')->group(function () {

    // CRUD
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // DISCOVER
    Route::get('/categories/discover', [CategoryController::class, 'discover'])
        ->name('categories.discover');

    // SHARE (forced subscription)
    Route::post('/categories/{category}/share', [CategorySubscriptionController::class, 'forceSubscribe'])
        ->name('categories.share');

    // SUBSCRIBE (self-subscribe)
    Route::post('/categories/{category}/subscribe', [CategorySubscriptionController::class, 'subscribe'])
        ->name('categories.subscribe');

    // UNSUBSCRIBE (self-unsubscribe)
    Route::post('/categories/{category}/unsubscribe', [CategorySubscriptionController::class, 'unsubscribe'])
        ->name('categories.unsubscribe');
});

require __DIR__.'/auth.php';
