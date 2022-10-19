<?php

namespace App\Core\Mappers;

use App\Core\Models\RawData\Order;

/**
 * Interface OrderProcessorInterface
 * @package App\Core\Mappers
 */
interface OrderProcessorInterface
{
    /**
     * Process the order
     *
     * @param Order $order
     * @return Order
     */
    public function processOrder(Order $order): Order;
}
