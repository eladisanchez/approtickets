<?php

use Illuminate\Support\Facades\Route;
use Eladisanchez\ApproTickets\Controllers\OrderController;

Route::get('/orders', [OrderController::class, 'index']);