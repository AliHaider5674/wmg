<?php
namespace App\Models\Service\Model;

use App\Models\Service\Model\Serialize;
use App\Models\Service\Model\ShipmentLineChange\Item;

/**
 * Shipment change status
 *
 * Reference
 * https://omsdocs.magento.com/en/specifications/#magento.logistics.shipment_request_management
 *
 * Class PackageModel
 * @category WMG
 * @package  App\Models\Service\Model\Shipment
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentLineChange extends Serialize
{
    public $shipmentRequestId; //Required
    /** @var \App\Models\Service\Model\ShipmentLineChange\Item[] */
    public $items = []; //Required
    public $user;

    public function newItem()
    {
        $item = new Item();
        $this->items[] = $item;
        return $item;
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
