<?php
use App\SMS\Constants\ConfigConstant;

/** @var \WMGCore\Services\ConfigService $configService */
$configService = app()->get(\WMGCore\Services\ConfigService::class);
return [
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'sms_s3' => [
        'driver' => 's3',
        'key' => $configService->get(ConfigConstant::SMS_AWS_ACCESS_KEY_ID, ''),
        'secret' => $configService->get(ConfigConstant::SMS_AWS_ACCESS_SECRET_KEY, ''),
        'region' => $configService->get(ConfigConstant::SMS_AWS_DEFAULT_REGION, ''),
        'bucket' => $configService->get(ConfigConstant::SMS_AWS_S3_BUCKET_NAME, ''),
        'url' => $configService->get(ConfigConstant::SMS_AWS_URL, ''),
    ],
    'sms_sftp' => [
        'driver' => 'sftp',
        'host' =>  $configService->get(ConfigConstant::SMS_SFTP_HOST, 'sftp.us.prod.wmgecom.com'),
        'username' => $configService->get(ConfigConstant::SMS_SFTP_USERNAME, 'service.fulfillment'),
        'password' => $configService->get(ConfigConstant::SMS_SFTP_PASSWORD),
        'port' => $configService->get(ConfigConstant::SMS_SFTP_PORT, '22'),
        'privateKey' => $configService->get(ConfigConstant::SMS_SFTP_PRIVATE_KEY),
        'timeout' => $configService->get(ConfigConstant::SMS_SFTP_TIMEOUT, 60),
        'cache' => [
            'store' => 'redis',
            'expire' => 60,
            'prefix' => 'SMS_SFTP',
        ],
    ],
    'sms_local' => [
        'driver' => 'local',
        'root' => storage_path(env('SMS_LOCAL_ROOT', 'sms')),
    ],

    'sms_test_local' => [
        'driver' => 'local',
        'root' => app_path('SMS/Tests/Feature/files/local')
    ],

    'mes_test_remote' => [
        'driver' => 'local',
        'root' => app_path('SMS/Tests/Feature/files/remote')
    ]
];
