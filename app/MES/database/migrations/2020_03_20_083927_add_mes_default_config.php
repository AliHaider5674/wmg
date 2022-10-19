<?php

use WMGCore\Models\AbstractConfigMigration;
use App\MES\Constants\ConfigConstant;

/**
 * Add MES Source Map
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddMesDefaultConfig extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::MES_ORDER_BATCH_SIZE, 800);
        $this->addConfig(ConfigConstant::STOCK_CRON, '0 1 * * *');
    }
}
