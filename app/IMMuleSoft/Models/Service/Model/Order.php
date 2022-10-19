<?php

namespace App\IMMuleSoft\Models\Service\Model;

use App\Models\Service\Model\Serialize;

/**
 * Class Order
 * @package App\IMMuleSoft\Models\Service\Model
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Order extends Serialize
{
    public array $orders;

    /**
     * setHiddenOrderIds
     * @param array $orderIds
     * @return Order
     */
    public function setHiddenOrderIds(array $orderIds): Order
    {
        return $this->setHiddenData('order_ids', $orderIds);
    }

    /**
     * getHiddenOrderIds
     * @return mixed|null
     */
    public function getHiddenOrderIds()
    {
        return $this->getHiddenData('order_ids');
    }
}
