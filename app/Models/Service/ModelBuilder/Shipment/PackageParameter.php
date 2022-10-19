<?php
namespace App\Models\Service\ModelBuilder\Shipment;

use App\Models\Service\Model\Serialize;

/**
 * Package Parameter
 *
 * Class ItemParameter
 * @category WMG
 * @package  App\Models\Service\ModelBuilder\ShipmentLineChange
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class PackageParameter extends Serialize
{
    /** @var ItemParameter[] */
    public $items = [];
    public $details;
    public $carrier;
    public $trackingNumber;
    public $trackingLink;
    public $trackingComment;
    public $shippingLabelLink;
    public $packageId;
    /** @var int[] internal order item id */
    public $itemIds = [];
    /** @var int[] Quantity Shipped Per Line Item*/
    public $shippedQtyMap = [];

    public function addHiddenItem(ItemParameter $item)
    {
        $items = $this->getHiddenData('items') ? : [];
        $items[] = $item;
        $this->setHiddenData('items', $items);
    }

    public function getHiddenItems()
    {
        return $this->getHiddenData('items') ? : [];
    }
}
