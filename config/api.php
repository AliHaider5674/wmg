<?php

return [

    /**
     * EU Ingram Micro Warehouse API connection
     */

    'ingram' => [
        /**
         * Ingram Micro Base API endpoint
         */
        'url' => env('IM_API_BASE_URL'),

        /**
         * Ingram Micro API username
         */
        'user' => env('IM_API_USER_NAME'),

        /**
         * Ingram Micro API password
         */
        'password' => env('IM_API_USER_PASSWORD')
    ]
];
