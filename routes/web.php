<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubscriptionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentItemController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RollupController;

// Welcome page (public)
Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


// Authenticated application routes
Route::middleware('auth')->group(function () {

    Route::get('/import', [\App\Http\Controllers\ImportController::class, 'index']);
    Route::post('/import', [\App\Http\Controllers\ImportController::class, 'store']);

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
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
    
    // ROLLUPS
    Route::prefix('rollups')->name('rollups.')->group(function () {
        Route::get('/', [RollupController::class, 'index'])->name('index');
        Route::get('/create', [RollupController::class, 'create'])->name('create');
        Route::post('/', [RollupController::class, 'storeRoot'])->name('storeRoot');
        Route::post('/{parent}/children', [RollupController::class, 'storeChild'])->name('storeChild');
        Route::get('/{rollup}/edit', [RollupController::class, 'edit'])->name('edit');
        Route::put('/{rollup}', [RollupController::class, 'update'])->name('update');
        Route::delete('/{rollup}', [RollupController::class, 'destroy'])->name('destroy');

        Route::post('/{rollup}/users', [RollupController::class, 'attachUser'])->name('attachUser');
        Route::delete('/{rollup}/users/{user}', [RollupController::class, 'detachUser'])
            ->middleware('can:detachUser,rollup,user')
            ->name('detachUser');

        Route::post('/{rollup}/categories', [RollupController::class, 'attachCategory'])->name('attachCategory');
        Route::delete('/{rollup}/categories/{category}', [RollupController::class, 'detachCategory'])->name('detachCategory');
    });

    // PROFILES
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/deputies', [ProfileController::class, 'storeDeputy'])->name('profile.deputies.store');
    Route::delete('/profile/deputies/{deputy}', [ProfileController::class, 'destroyDeputy'])->name('profile.deputies.destroy');

/*    // AUTHENTICATION
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
*/
});

require __DIR__.'/auth.php';
