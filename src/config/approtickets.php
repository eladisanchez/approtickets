<?php

return [

    'timeout' => [
        'ticket' => env('AT_TIMEOUT_TICKET', 10),
        'payment' => env('AT_TIMEOUT_PAYMENT', 15),
    ],

    'admin' => [
        'colors' => [
            'primary' => env('AT_COLORS_PRIMARY', '#5cacb0'),
        ],
        'font' => env('AT_FONT', 'Inter'),
    ],

    'payment_methods' => [
        'card' => 'Targeta de crèdit',
        'credit' => 'Crèdit',
        'cash' => 'Efectiu',
        'santander' => 'Santander'
    ],

];
