<?php

namespace App\Core\Services;

use App\Core\Mappers\OrderProcessorInterface;
use App\Core\Models\RawData\Order;

/**
 * Map order data
 *
 * Class OrderMapperService
 * @category WMG
 * @package  App\Core\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com'
 */
class OrderRawMapperService
{
    /**
     * @var OrderProcessorInterface[]
     */
    private $processors;

    /**
     * OrderRawMapperService constructor.
     * @param iterable $processors
     */
    public function __construct(iterable $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @param Order $order
     * @return Order
     */
    public function processOrder(Order $order): Order
    {
        return $this->process('processOrder', $order);
    }

    /**
     * @param string $method
     * @param Order  $order
     * @return Order
     */
    private function process(string $method, Order $order): Order
    {
        $processedOrder = $order;

        foreach ($this->processors as $processor) {
            $processedOrder = $processor->{$method}($processedOrder);
        }

        return $processedOrder;
    }
}
