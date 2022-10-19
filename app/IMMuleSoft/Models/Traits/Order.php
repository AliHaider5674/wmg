<?php

namespace App\IMMuleSoft\Models\Traits;

use App\Models\OrderItem;

trait Order
{
    /**
     * updateOrderStatus
     * @param array $orderIds
     * @param int $orderStatus
     * @param int $orderItemStatus
     */
    public function updateOrderStatus(array $orderIds, int $orderStatus, int $orderItemStatus)
    {
        if (empty($orderIds)) {
            return;
        }

        $orderQuery = \App\Models\Order::query();
        $orderQuery->whereIn('id', $orderIds)
            ->update(['status'=> $orderStatus]);

        $orderItemQuery = OrderItem::query();
        $orderItemQuery->whereIn('parent_id', $orderIds)
            ->update(['drop_status'=> $orderItemStatus]);
    }
}
