<?php

return [

    // Vistes en Inertia
    'inertia' => env('AT_INERTIA', false),

    // Apartats principals del web. Deixar en false si no en té
    'sections' => explode(',', env('AT_SECTIONS', false)),

    
    'timeout' => [
        // Temps que duraran al cistell les entrades
        'ticket' => env('AT_TIMEOUT_TICKET', 10),
        // Temps per fer el pagament des que la comanda s'ha guardat
        'payment' => env('AT_TIMEOUT_PAYMENT', 60),
    ],

    'admin' => [
        'colors' => [
            // Color dels botons de Filament
            'primary' => env('AT_ADMIN_COLOR', '#5cacb0'),
        ],
        // Tipografia a Filament
        'font' => env('AT_ADMIN_FONT', 'Inter'),
    ],

    // Pagaments acceptats
    'payment_methods' => [
        'card' => 'Targeta de crèdit',
        'credit' => 'Crèdit',
        'cash' => 'Efectiu',
        'santander' => 'Santander'
    ],

    // Les activitats poden tenir idiomes
    'languages' => false,

    // Idiomes del web
    'locales' => explode(',', env('AT_LOCALES', 'ca,es'))

];
