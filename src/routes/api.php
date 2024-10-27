<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ApproTickets\Http\Controllers\QrController;

Route::middleware('api')
    ->prefix('api')
    ->group(function () {

        Route::post('/login', [QrController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', function (Request $request) {
                return $request->user();
            });
            Route::post('/testQR', [QrController::class, 'testQr']);
            Route::post('/checkQR', [QrController::class, 'checkQr']);
        });

    });