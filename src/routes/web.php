<?php

use Illuminate\Support\Facades\Route;
use ApproTickets\Controllers\CartController;
use ApproTickets\Controllers\OrderController;
use ApproTickets\Controllers\ProductController;
use ApproTickets\Controllers\TPVController;

// Cart
Route::get('cistell', [CartController::class, 'show'])->name('cart');
Route::post('cistell', [CartController::class, 'add'])->name('cart.add');
Route::delete('cistell', [CartController::class, 'removeRow'])->name('cart.remove');
Route::get('cistell/destroy', [CartController::class, 'destroy'])->name('cart.destroy');

// Checkout
Route::get('confirmar', [CartController::class, 'confirm'])->name('checkout');
Route::post('confirmar', [OrderController::class, 'store'])->name('order.store');
Route::get('pagament/{id}', [OrderController::class, 'payment'])->name('order.payment');
Route::get('pagament/{session}/{id}/gracies', [OrderController::class, 'thanks'])->name('order.thanks');
Route::get('pagament/{session}/{id}/error', [OrderController::class, 'error'])->name('order.error');
Route::get('pdf/order/{session}/{id}', [OrderController::class, 'pdf'])->name('order.pdf');

// Products
Route::get('activitat/{name}/{day?}/{hour?}', [ProductController::class, 'show'])->name('product')
    ->where('name', '[a-z0-9-]+')
    ->where('day', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->where('hour', '[0-9]{2}:[0-9]{2}');
Route::get('availability/{id}/{day}/{hour}', [ProductController::class, 'availability'])->name('product.availability');
Route::get('image/{path}', [ProductController::class, 'image'])->name('image')->where('path', '.*\.(jpg|jpeg|png|gif|bmp|webp)');

Route::post('tpv-notification', [TPVController::class, 'notification'])->name('tpv-notification');