<?php

return [

    'timeout' => [
        'ticket' => env('AT_TIMEOUT_TICKET', 10),
        'payment' => env('AT_TIMEOUT_PAYMENT', 15),
    ],

    'colors' => [
        'primary' => env('AT_COLORS_PRIMARY', '#5cacb0'),
    ],

];