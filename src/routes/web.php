<?php

use Illuminate\Support\Facades\Route;
use ApproTickets\Controllers\CartController;

// Cart
Route::get('cistell', [CartController::class, 'show'])->name('cart');
Route::post('cistell', [CartController::class, 'add'])->name('cart.add');
Route::delete('cistell', [CartController::class, 'removeRow'])->name('cart.remove');
Route::get('cistell/destroy', [CartController::class, 'destroy'])->name('cart.destroy');