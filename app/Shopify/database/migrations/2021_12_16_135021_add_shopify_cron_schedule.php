<?php

use WMGCore\Models\AbstractConfigMigration;
use App\Shopify\Constants\ConfigConstant;

/**
 * Class CreateShopifyOrdersTable
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddShopifyCronSchedule extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::ORDER_FETCH_CRON, '*/10 * * * *');
        $this->addConfig(ConfigConstant::ORDER_EXPAND_CRON, '*/5 * * * *');
        $this->addConfig(ConfigConstant::ORDER_SCAN_CRON, '*/5 * * * *');
        $this->addConfig(ConfigConstant::FULFILLMENT_REQUEST_CRON, '*/30 * * * *');
    }
}
