<?php

return [
    'directories' => [
        'shipment' => [
            'live' => env('MES_LIVE_SHIPMENT_DIR', 'DESADV'),
            // Config for faker does not have default
            'live-fake' => env('MES_LIVE_SHIPMENT_DIR'),
            'tmp' => env('MES_TMP_SHIPMENT_DIR', 'DESADV'),
            'history' => env('MES_HISTORY_SHIPMENT_DIR', 'DESADV/DONE'),
        ],
        'ack' => [
            'live' => env('MES_LIVE_ACK_DIR', 'OTRCK'),
            // Config for faker does not have default
            'live-fake' => env('MES_LIVE_ACK_DIR'),
            'tmp' => env('MES_TMP_ACK_DIR', 'OTRCK'),
            'history' => env('MES_HISTORY_ACK_DIR', 'OTRCK/DONE'),
        ],
        'order' => [
            'live' => env('MES_LIVE_ORDER_DIR', 'ORDER'),
            'tmp' => env('MES_TMP_ORDER_DIR', 'ORDER'),
        ],
        'stock' => [
            'live' => env('MES_LIVE_STOCK_DIR', 'STOCK'),
            'tmp' => env('MES_TMP_STOCK_DIR', 'STOCK'),
            'history' => env('MES_HISTORY_STOCK_DIR', 'STOCK/DONE'),
        ],
    ],
    'connections' => [
        'remote' => env('MES_REMOTE_CONNECTION', 'mes_sftp'),
        'local' => env('MES_LOCAL_CONNECTION', 'mes_local'),
    ],
];
