<?php

declare(strict_types=1);

return [

    'base_url' => env('AWAJDIGITAL_BASE_URL', 'https://api.awajdigital.com/api'),

    'token' => env('AWAJDIGITAL_TOKEN'),

    'default_sender' => env('AWAJDIGITAL_SENDER'),

    'timeout' => env('AWAJDIGITAL_TIMEOUT', 30),

    'retry' => [
        'times' => 2,
        'sleep_ms' => 200,
    ],

];
