<?php

return [

    'inertia' => env('AT_INERTIA', false),

    'timeout' => [
        'ticket' => env('AT_TIMEOUT_TICKET', 10),
        'payment' => env('AT_TIMEOUT_PAYMENT', 60),
    ],

    'admin' => [
        'colors' => [
            'primary' => env('AT_ADMIN_COLOR', '#5cacb0'),
        ],
        'font' => env('AT_ADMIN_FONT', 'Inter'),
    ],

    'payment_methods' => [
        'card' => 'Targeta de crèdit',
        'credit' => 'Crèdit',
        'cash' => 'Efectiu',
        'santander' => 'Santander'
    ],

    'languages' => false,

    'locales' => explode(',', env('AT_LOCALES', 'ca,es'))

];
