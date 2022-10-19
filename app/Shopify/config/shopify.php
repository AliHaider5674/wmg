<?php
return [
    'secret' => env('SHOPIFY_API_SECRET', null),
    'routes' => [
        'signature-header' => 'HTTP_X_SHOPIFY_HMAC_SHA256'
        ]
];
