<?php

return [

    'timeout' => [
        'ticket' => env('AT_TIMEOUT_TICKET', 10),
        'payment' => env('AT_TIMEOUT_PAYMENT', 60),
    ],

    'admin' => [
        'colors' => [
            'primary' => '#5cacb0',
        ],
        'font' => 'Inter',
    ],

    'payment_methods' => [
        'card' => 'Targeta de crèdit',
        'credit' => 'Crèdit',
        'cash' => 'Efectiu',
        'santander' => 'Santander'
    ],

    'languages' => false

];
