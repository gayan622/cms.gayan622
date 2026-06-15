<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Filament registers its own routes via AdminPanelProvider.
| This file only needs the health-check and any non-API web routes.
*/

Route::get('/', function () {
    return response()->json([
        'name'    => config('app.name'),
        'version' => '1.0.0',
        'status'  => 'running',
        'admin'   => url('/admin'),
        'api'     => url('/api'),
    ]);
});
