<?php

use WMGCore\Models\AbstractConfigMigration;
use App\Mdc\Constants\ConfigConstant;

/**
 * Add MES Source Map
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddMdcInitialConfig extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::MDC_STOCK_SOURCE_IDS, ['US']);
        $this->addConfig(ConfigConstant::MDC_ALLOW_STOCK_SOURCE_OVERLAP, 0);
    }
}
