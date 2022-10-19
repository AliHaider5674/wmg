<?php
use WMGCore\Models\AbstractConfigMigration;
use App\Shopify\Constants\ConfigConstant;

/**
 * Class AddShopifyFailOrderCronSchedule
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class AddShopifyFailOrderCronSchedule extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::ORDER_FETCH_FAILED_FULFILLMENT_ORDERS_CRON, '*/45 * * * *');
    }
}
