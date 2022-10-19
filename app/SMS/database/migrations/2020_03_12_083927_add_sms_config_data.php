<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WMGCore\Models\AbstractConfigMigration;
use App\SMS\Constants\ConfigConstant;

/**
 * Add initial config data
 *
 * Class AddSmsConfigData
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddSmsConfigData extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::SMS_HISTORY_STOCK_DIR, 'dropoff/done');
        $this->addConfig(ConfigConstant::SMS_LIVE_STOCK_DIR, 'dropoff');
        $this->addConfig(ConfigConstant::SMS_REMOTE_CONNECTION, 'sms_s3');
        $this->addConfig(ConfigConstant::SMS_LOCAL_CONNECTION, 'sms_local');
        $this->addConfig(ConfigConstant::SMS_TMP_STOCK_DIR, 'dropoff');


        $this->addConfig(ConfigConstant::SMS_AWS_ACCESS_KEY_ID, '');
        $this->addConfig(ConfigConstant::SMS_AWS_ACCESS_SECRET_KEY, '');
        $this->addConfig(ConfigConstant::SMS_AWS_S3_BUCKET_NAME, '');
        $this->addConfig(ConfigConstant::SMS_AWS_URL, 's3.us-east-1.amazonaws.com');
        $this->addConfig(ConfigConstant::SMS_AWS_DEFAULT_REGION, 'us-east-1');
    }
}
