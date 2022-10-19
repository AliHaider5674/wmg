<?php

namespace App\Core\Mappers;

use App\Core\Constants\ConfigConstant;
use App\Core\Models\RawData\Order;
use WMGCore\Services\ConfigService;

/**
 * Map order data from config
 *
 * Class BaseOrderMapper
 * @category WMG
 * @package  App\Core\Mappers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class ShippingOrderMapper implements OrderProcessorInterface
{
    /**
     * @var ConfigService
     */
    protected $configService;

    /**
     * @var array
     */
    protected $shippingMethodMap;

    /**
     * ShippingOrderMapper constructor.
     * @param ConfigService $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @param Order $order
     * @return Order
     */
    public function processOrder(Order $order): Order
    {
        $order->shippingMethod = $this->getShippingMethod($order);
        return $order;
    }

    /**
     * @param Order $order
     * @return mixed|null
     */
    protected function getShippingMethod(Order $order): ?string
    {
        $shippingMethodMap = $this->getShippingMethodMap();
        $shippingMethod = $order->shippingMethod;

        return $shippingMethodMap[$shippingMethod]
            ?? $shippingMethodMap['*']
            ?? null;
    }

    /**
     * @return array
     */
    protected function getShippingMethodMap(): array
    {
        return $this->shippingMethodMap
            ?? $this->shippingMethodMap = $this->configService->getJson(ConfigConstant::SHIPPING_METHOD_MAP, []);
    }
}
