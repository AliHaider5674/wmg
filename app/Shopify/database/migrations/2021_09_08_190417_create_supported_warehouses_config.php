<?php declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use WMGCore\Models\AbstractConfigMigration;
use App\Shopify\Constants\ConfigConstant;

/**
 * Class CreatePrintfulEventsTable
 * @package App\Printful
 */
class CreateSupportedWarehousesConfig extends AbstractConfigMigration
{
    protected function setupConfig()
    {
        $this->addConfig(ConfigConstant::SUPPORTED_WAREHOUSES, [
            'GNAR', 'US', 'IM'
        ]);
    }
}
