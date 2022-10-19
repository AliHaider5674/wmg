<?php

return [
    'order' => [
        'tmp-dir' => env('MES_TMP_ORDER_DIR', 'ORDER'),
        'live-dir' => env('MES_LIVE_ORDER_DIR', 'ORDER'),
    ],
    'shipment' => [
        'history-dir' => env('MES_HISTORY_SHIPMENT_DIR', 'DESADV/DONE'),
        'live-dir' => env('MES_LIVE_SHIPMENT_DIR', 'DESADV'),
        'tmp-dir' => env('MES_TMP_SHIPMENT_DIR', 'DESADV'),
    ],
    'ack' => [
        'history-dir' => env('MES_HISTORY_ACK_DIR', 'OTRCK/DONE'),
        'live-dir' => env('MES_LIVE_ACK_DIR', 'OTRCK'),
        'tmp-dir' => env('MES_TMP_ACK_DIR', 'OTRCK'),
    ],
    'stock' => [
        'history-dir' => env('MES_HISTORY_STOCK_DIR', 'STOCK/DONE'),
        'live-dir' => env('MES_LIVE_STOCK_DIR', 'STOCK'),
        'tmp-dir' => env('MES_TMP_STOCK_DIR', 'STOCK'),
        'batch-process-id-key' => env('MES_STOCK_BATCH_PROCESS_ID_KEY'),
    ],
    'remote-connection' => env('MES_REMOTE_CONNECTION', 'mes_sftp'),
    'local-connection' => env('MES_LOCAL_CONNECTION', 'mes_local'),
];
