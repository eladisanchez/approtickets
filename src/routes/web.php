<?php

use Illuminate\Support\Facades\Route;
use ApproTickets\Controllers\OrderController;

Route::get('/orders', [OrderController::class, 'index']);