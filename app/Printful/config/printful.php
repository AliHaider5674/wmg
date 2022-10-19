<?php

// Printful Configuration
return [
    'api' => [
        'url' => env('PRINTFUL_API_URL', 'https://api.printful.com/'),
        'key' => env('PRINTFUL_API_KEY'),
        'enabled-webhooks' => [
            'package_shipped',
            'package_returned',
            'order_put_hold',
        ],
        'whitelisted-ips' => env('WHITELISTED_IPS', '[]'),
    ],
    'order' => [
        // Whether to create order as confirmed (true) or in draft status (false)
        'confirm' => env('PRINTFUL_SHOULD_CONFIRM_ORDER')
            ?? (env('APP_ENV') === 'production'),
    ],
];
