<?php

namespace App\Models\Service\Model;

/**
 * Class ShipmentRequest
 * @package App\Models\Service\Model
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ShipmentRequest extends Serialize
{
    public string $shipmentRequestId; //Required
    public array $items = []; //Required

    public function __construct($shipmentRequestId)
    {
        $this->shipmentRequestId = $shipmentRequestId;
    }

    public function addItem($orderLineId)
    {
        $this->items[] = $orderLineId;
    }

    public function getHiddenOrderId()
    {
        return $this->getHiddenData('order_id');
    }

    public function setHiddenOrderId($orderId)
    {
        return $this->setHiddenData('order_id', $orderId);
    }
}
