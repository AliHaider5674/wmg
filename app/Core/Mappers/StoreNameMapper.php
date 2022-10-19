<?php
namespace App\Core\Mappers;

use App\Core\Models\RawData\Order;

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
class StoreNameMapper implements OrderProcessorInterface
{
    /**
     * @param Order $order
     * @return Order
     */
    public function processOrder(Order $order): Order
    {
        $order->storeName = $this->getStoreName($order);

        return $order;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'core.store';
    }

    /**
     * Get store name
     * @param Order $order
     * @return string
     */
    protected function getStoreName(Order $order)
    {
        $customAttributes = $order->customAttributes;
        $storeName = isset($customAttributes['store_name']) ? $customAttributes['store_name'] : null;
        if (!$storeName) {
            $name = $order->salesChannel;
            preg_match('/([a-zA-Z0-9]*)[\_\-]{1}(.*)/', $order->salesChannel, $matches);
            if (count($matches) === 3) {
                $name = $matches[2];
            }
            $storeName = ucwords(preg_replace('/[\_\- ]{1,}/', ' ', $name));
        }
        return $storeName;
    }
}
