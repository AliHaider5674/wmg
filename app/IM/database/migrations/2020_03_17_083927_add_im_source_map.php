<?php

use WMGCore\Models\AbstractConfigMigration;
use App\IM\Constants\ConfigConstant;

/**
 * Add IM Source Map
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddImSourceMap extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::IM_SOURCE_MAP, ['IM']);
    }
}
