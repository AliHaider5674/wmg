<?php
namespace Tests\Feature;

use App\IM\Constants\ConfigConstant as IMConfigConstant;
use App\MES\Constants\ConfigConstant as MesConfigConstant;
use App\Models\OrderItem;
use Tests\TestCase;
use WMGCore\Services\ConfigService;

/**
 * Basic Warehouse Test cases
 *
 * Class WarehouseTestCase
 * @category WMG
 * @package  Tests\Feature
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class WarehouseTestCase extends TestCase
{
    public function setUp():void
    {
        parent::setUp();
        //SETUP SOURCE MAP
        $configService = app()->make(ConfigService::class);
        $configService->update(IMConfigConstant::IM_SOURCE_MAP, ['EU']);
        $configService->update(MesConfigConstant::MES_SOURCE_MAP, ['US', 'GNAR']);
    }

    /**
     * Check if all shipped
     * @param $orders
     * @return bool
     */
    protected function isAllShipped($orders)
    {
        return $this->isAllFulfill($orders, 'quantity_shipped');
    }

    /**
     * Check if all items are ack
     * @param $orders
     * @return bool
     */
    protected function isAllAck($orders)
    {
        return $this->isAllFulfill($orders, 'quantity_ack');
    }


    /**
     * Check if all items are ack
     * @param $orders
     * @return bool
     */
    protected function isAllBackOrdered($orders)
    {
        return $this->isAllFulfill($orders, 'quantity_backordered');
    }

    protected function isAllFulfill($orders, $property = 'quantity_ack')
    {
        foreach ($orders as $order) {
            foreach ($order->orderItems()->get() as $orderItem) {
                /**@var OrderItem $orderItem  */
                if (!$orderItem->is_shippable) {
                    continue;
                }

                $isAllFulfilled = $orderItem->getAttribute($property) >= $orderItem->getAttribute('quantity');
                if (!$isAllFulfilled) {
                    return false;
                }
            }
        }
        return true;
    }
}
