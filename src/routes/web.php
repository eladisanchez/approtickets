<?php

use Illuminate\Support\Facades\Route;
use ApproTickets\Http\Controllers\CartController;
use ApproTickets\Http\Controllers\OrderController;
use ApproTickets\Http\Controllers\ProductController;
use ApproTickets\Http\Controllers\PageController;
use ApproTickets\Http\Middleware\HandleInertiaRequests;
use ApproTickets\Http\Controllers\CalendarController;
use ApproTickets\Http\Controllers\RefundController;
use ApproTickets\Http\Controllers\PackController;
use ApproTickets\Http\Controllers\PaymentController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


Route::group(['prefix' => LaravelLocalization::setLocale()], function () {

    Route::middleware([
        'web',
        HandleInertiaRequests::class
    ])->group(function () {

        // Home
        Route::get('/', [PageController::class, 'home'])->name('home');

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

        // User area
        Route::post('login', [OrderController::class, 'login'])->name('login');

        // Refunds
        Route::get('devolucio/{hash}', [RefundController::class, 'show'])->name('refund');

        // Payments
        Route::get('pagament/{hash}', [PaymentController::class, 'show'])->name('payment');

        // Products
        Route::get('activitat/{name}/{day?}/{hour?}', [ProductController::class, 'show'])->name('product')
            ->where('name', '[a-z0-9-]+')
            ->where('day', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
            ->where('hour', '[0-9]{2}:[0-9]{2}');
        Route::get('availability/{id}/{day}/{hour}', [ProductController::class, 'availability'])->name('product.availability');
        Route::get('image/{path}', [ProductController::class, 'image'])->name('image')
            ->where('path', '.*\.(jpg|jpeg|png|gif|bmp|webp)');
        Route::get('search', [ProductController::class, 'search'])->name('search');

        // Packs
        Route::post('reserva-pack/{packId}', [PackController::class, 'start'])->name('pack.start');
        Route::post('cancelar-pack', [PackController::class, 'cancel'])->name('pack.cancel');

        // Calendar
        Route::get('calendari', [CalendarController::class, 'calendar'])->name('calendar');
        Route::get('calendari/ics', [CalendarController::class, 'ics']);

        // Static pages
        Route::get('pagina/{slug}', [PageController::class, 'page'])->name('page');

    });

});

Route::middleware([
    'web'
])->group(function () {

    Route::get('prova', function () {
        return 'prova';
    })->name('provaget');
    Route::post('prova', function () {
        return 'prova';
    })->name('prova');

    // Tpv


});