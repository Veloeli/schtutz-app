<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubscriptionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentItemController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamUserController;

Route::redirect('/', '/documents');

// Dashboard
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Authenticated application routes
Route::middleware('auth')->group(function () {

    Route::prefix('teams')->name('teams.')->group(function () {

        // Team CRUD
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('/create', [TeamController::class, 'create'])->name('create');
        Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::put('/{team}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');

        // Membership CRUD
        Route::get('/{team}/memberships', [TeamUserController::class, 'index'])->name('memberships.index');
        Route::get('/{team}/memberships/create', [TeamUserController::class, 'create'])->name('memberships.create');
        Route::post('/{team}/memberships', [TeamUserController::class, 'store'])->name('memberships.store');
        Route::get('/{team}/memberships/{membership}/edit', [TeamUserController::class, 'edit'])->name('memberships.edit');
        Route::put('/{team}/memberships/{membership}', [TeamUserController::class, 'update'])->name('memberships.update');
        Route::delete('/{team}/memberships/{membership}', [TeamUserController::class, 'destroy'])->name('memberships.destroy');

        // Search helper
        Route::get('/{team}/search-users', [TeamUserController::class, 'search'])->name('search-users');
    });

    // DOCUMENTS
    Route::resource('documents', DocumentController::class);
    Route::resource('documents.items', DocumentItemController::class);
    Route::post('/documents/toggle', [DocumentController::class, 'toggle'])
        ->name('documents.toggle');

    // CATEGORIES
    Route::prefix('categories')->name('categories.')->group(function () {

        // CRUD
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

    });
});

require __DIR__.'/auth.php';
