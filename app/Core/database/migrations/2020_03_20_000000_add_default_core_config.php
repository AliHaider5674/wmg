<?php

use WMGCore\Models\AbstractConfigMigration;
use App\Core\Constants\ConfigConstant;

/**
 * Add MES Source Map
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddDefaultCoreConfig extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::FULFILLMENT_ACK_CRON, '*/5 * * * *');
        $this->addConfig(ConfigConstant::FULFILLMENT_ORDER_CRON, '*/5 * * * *');
        $this->addConfig(ConfigConstant::FULFILLMENT_SHIPMENT_CRON, '*/5 * * * *');
        $this->addConfig(ConfigConstant::FULFILLMENT_DIGITAL_CRON, '*/5 * * * *');
    }
}
