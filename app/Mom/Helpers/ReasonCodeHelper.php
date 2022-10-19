<?php
namespace App\Mom\Helpers;

use App\Mom\Constants\ConfigurationConstant;
use WMGCore\Services\ConfigService;
use App\Models\Service\Model\ShipmentLineChange\Item as LineChangeItem;

/**
 * Helper to handle reason code
 * from configuration
 *
 * Class ReasonCodeHelper
 * @category WMG
 * @package  App\Mom\Helpers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ReasonCodeHelper
{
    private $configService;
    const UNKNOWN_STATUS_CODE = 'Unknown';
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function getStatusByCode($statusCode)
    {
        $map = $this->configService->getJson(ConfigurationConstant::REASON_CODE_ORDER_STATUS_MAP, []);
        return isset($map[$statusCode]) ? $map[$statusCode] : LineChangeItem::STATUS_RECEIVED_BY_LOGISTICS;
    }

    public function isBackorder($statusCode)
    {
        $map = $this->configService->getJson(ConfigurationConstant::REASON_CODE_BACKORDER_MAP, []);
        return in_array($statusCode, $map);
    }

    public function getStatusCodeName($statusCode)
    {
        $map = $this->configService->getJson(ConfigurationConstant::REASON_CODE_TITLE_MAP, []);
        return array_key_exists($statusCode, $map) ? $map[$statusCode] : self::UNKNOWN_STATUS_CODE;
    }
}
