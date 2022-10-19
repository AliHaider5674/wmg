<?php
namespace App\SMS\Constants;

/**
 * SMS config constants
 *
 * Class ConfigConstant
 * @category WMG
 * @package  App\SMS\Constants
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class ConfigConstant
{
    const SMS_HISTORY_STOCK_DIR = 'sms.history.stock.dir';
    const SMS_LIVE_STOCK_DIR = 'sms.live.stock.dir';
    const SMS_TMP_STOCK_DIR = 'sms.tmp.stock.dir';
    const SMS_REMOTE_CONNECTION = 'sms.remote.connection';
    const SMS_LOCAL_CONNECTION = 'sms.local.connection';
    const SMS_AWS_ACCESS_KEY_ID = 'sms.aws.access.key.id';
    const SMS_AWS_ACCESS_SECRET_KEY = 'sms.aws.access.secret.key';
    const SMS_AWS_S3_BUCKET_NAME = 'sms.aws.s3.bucket.name';
    const SMS_AWS_DEFAULT_REGION = 'sms.aws.default.region';
    const SMS_AWS_URL = 'sms.aws.url';
    const SMS_FILE_PATTERN = 'sms.file.pattern';

    const SMS_SFTP_HOST = 'sms.sftp.host';
    const SMS_SFTP_USERNAME = 'sms.sftp.username';
    const SMS_SFTP_PASSWORD = 'sms.sftp.password';
    const SMS_SFTP_PORT = 'sms.sftp.port';
    const SMS_SFTP_PRIVATE_KEY = 'sms.sftp.private_key';
    const SMS_SFTP_TIMEOUT = 'sms.sftp.timeout';
}
