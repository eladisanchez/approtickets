<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ApproTickets\Http\Controllers\QrController;
use ApproTickets\Http\Controllers\TPVController;

Route::middleware('api')
    ->prefix('api')
    ->group(function () {

        Route::post('tpv-notification', [TPVController::class, 'notification'])
            ->withoutMiddleware(Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->name('tpv-notification');

        Route::post('/login', [QrController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', function (Request $request) {
                return $request->user();
            });
            Route::post('/testQR', [QrController::class, 'testQr']);
            Route::post('/checkQR', [QrController::class, 'checkQr']);
        });

    });