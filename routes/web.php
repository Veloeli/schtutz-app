<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentItemController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RollupController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\StockSplitController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CollectionController;

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

    // Teams
    Route::resource('teams', TeamController::class);
    Route::resource('teams.memberships', TeamUserController::class);

    // Documents
    Route::resource('documents', DocumentController::class);
    Route::resource('documents.items', DocumentItemController::class);
    Route::post('/documents/toggle', [DocumentController::class, 'toggle'])->name('documents.toggle');
    Route::get('/documents/{document}/suggest-category', [DocumentItemController::class, 'suggestCategory'])->name('documents.items.suggest-category');

    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Rollups
    Route::resource('rollups', RollupController::class);
    Route::prefix('rollups')->name('rollups.')->group(function () {
        Route::post('/', [RollupController::class, 'storeRoot'])->name('storeRoot');
        Route::post('/{parent}/children', [RollupController::class, 'storeChild'])->name('storeChild');
        Route::post('/{rollup}/categories', [RollupController::class, 'attachCategory'])->name('attachCategory');
        Route::delete('/{rollup}/categories/{category}', [RollupController::class, 'detachCategory'])->name('detachCategory');
    });

    // Securities
    Route::resource('securities', SecurityController::class);
    Route::resource('stock-splits', StockSplitController::class);
    Route::resource('quotes', QuoteController::class);
    Route::get('/quotes/import', [QuoteController::class, 'showImportForm'])->name('quotes.import');
    Route::post('/quotes/import', [QuoteController::class, 'import'])->name('quotes.import.process');

    Route::get('/balances', [BalanceController::class, 'index'])->name('balances.index');
//    Route::post('/balances/set-root', [BalanceController::class, 'setRoot'])->name('balances.setRoot');

    // Collections
    Route::resource('collections', CollectionController::class);

    // PROFILES
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

});

require __DIR__.'/auth.php';
