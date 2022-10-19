<?php

namespace App\IM\Handler\IO;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;

/**
 * Class ApiStock
 * Import stock from Ingram Micro Warehouse API
 *
 * @category WMG
 * @package  App\IM\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ApiAck extends ApiAbstract
{
    /**
     * @inheritdoc
     */
    public function start(array $data = null)
    {
        parent::start($data);
    }

    /**
     * @inheritdoc
     *
     */
    public function receive($callback)
    {
        $orderCollection = Order::with(['orderItems' => function ($query) {
            $query->physical()->whereRaw('quantity_ack < quantity')
                ->whereIn('source_id', $this->config->getSourceIds());
        }]);

        $orderCollection->chunk(100, function ($order) use ($callback) {
            $parameter = $this->getParameter($order, $order->items);
            call_user_func($callback, $parameter);
        });
    }


    protected function getParameter($order, $orderItems)
    {
        $parameter = new ShipmentLineChangeParameter();
        $parameter->orderId = $order->id;
        foreach ($orderItems as $orderItem) {
            $itemParameter = new ItemParameter();
            $itemParameter->orderItemId = $orderItem->id;
            $itemParameter->sku = $orderItem->sku;
            $itemParameter->quantity = $orderItem->quantity;
            $itemParameter->backorderQuantity = $orderItem->quantity_backordered;
            $itemParameter->backOrderReasonCode = '';
            $parameter->addItem($itemParameter);
        }
        return $parameter;
    }

    public function send($data, $callback = null)
    {
        // TODO: Implement send() method.
    }

    public function finish(array $data = null)
    {
        // TODO: Implement finish() method.
    }

    public function rollback(...$args)
    {
        // TODO: Implement rollback() method.
    }
}
