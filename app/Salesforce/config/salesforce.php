<?php

return [
    'auth' => [
        'grant_type' => env('SALESFORCE_AUTH_GRANT_TYPE', 'client_credentials'),
        'url' => env('SALESFORCE_AUTH_URL'),
        'scope' => env('SALESFORCE_AUTH_SCOPE')
    ],
    'api' => [
        'base_url' => env('SALESFORCE_API_BASE_URL')
    ]
];
