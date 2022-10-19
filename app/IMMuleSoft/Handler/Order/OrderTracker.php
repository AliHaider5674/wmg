<?php

namespace App\IMMuleSoft\Handler\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OrderTracker
 * @package App\IMMuleSoft\Handler\Order
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderTracker
{
    /**
     * @var array
     */
    private array $orders;
    private array $orderIds;
    private array $orderItemIds;

    /**
     * add
     */
    public function add(array $order, Order $orderModel)
    {
        $this->orders[] = $order;
        $this->orderIds[$orderModel->order_id] = $orderModel->id;
    }

    /**
     * getOrders
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * getOrderIds
     * @return array
     */
    public function getOrderIds(): array
    {
        return $this->orderIds;
    }

    public function getOrderItemIds(): array
    {
        return $this->orderItemIds;
    }

    /**
     * getCount
     */
    public function getCount() : int
    {
        return count($this->orders);
    }

    /**
     * reset
     */
    public function reset()
    {
        unset($this->orders);
        unset($this->orderIds);
        unset($this->orderItemIds);

        $this->orders = array();
        $this->orderIds = array();
        $this->orderItemIds = array();
    }
}
