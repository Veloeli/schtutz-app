<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('items');
});

use Illuminate\Support\Facades\DB;

Route::get('/db-check', function () {
    return [
        'env' => env('DB_DATABASE'),
        'config' => config('database.connections.mysql.database'),
        'actual' => DB::connection()->getDatabaseName(),
    ];
});

Route::get('/db-all', function () {
    return config('database.connections');
});
