<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ApproTickets\Controllers\QrController;

Route::middleware('api')
    ->group(function () {

        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');

        Route::post('/login', [QrController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/testQR', [QrController::class, 'testQr']);
            Route::post('/checkQR', [QrController::class, 'checkQr']);
        });

    });