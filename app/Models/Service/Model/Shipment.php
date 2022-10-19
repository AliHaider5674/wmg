<?php
namespace App\Models\Service\Model;

use Carbon\Carbon;
use App\Models\Service\Model\Shipment\Package;
use App\Models\Service\Model\Shipment\Item;

/**
 * This is external service request model
 * It is same structure as MOM's lines_shipped.
 * https://omsdocs.magento.com/en/specifications/#magento.logistics.warehouse_management
 *
 * Class Shipment
 * @category WMG
 * @package  App\Models\Service\Model
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Shipment extends Serialize
{
    public String $requestId;

    /** @var \App\Models\Service\Model\Shipment\Package[] */
    public $packages = [];
    /** @var Item[] */
    public $items = [];
    /** @var integer */
    public $parentOrderLineNumber;
    /** @var string */
    public $status = 'SHIPPED';
    /** @var string */
    public $statusReason = 'SHIPPED';
    /** @var string */
    public $statusDate;

    public function newPackage()
    {
        $package = new Package();
        $this->packages[] = $package;
        return $package;
    }

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

    public function setHiddenOrderNumber($orderNumber)
    {
        return $this->setHiddenData('order_number', $orderNumber);
    }

    public function getHiddenOrderNumber()
    {
        return $this->getHiddenData('order_number');
    }
}
