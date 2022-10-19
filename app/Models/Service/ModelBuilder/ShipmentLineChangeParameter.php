<?php
namespace App\Models\Service\ModelBuilder;

use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;

/**
 * Shipment Line parameter for build shipment line change
 * model that send to external services
 *
 * Class ShipmentLineChangeParameter
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentLineChangeParameter extends Parameter
{
    public $orderId;
    /** @var ItemParameter[]*/
    public $items = [];
    private $orderItemIds = [];

    public function addItem(ItemParameter $item)
    {
        $this->items[$item->orderItemId] = $item;
        $this->orderItemIds[] = $item->orderItemId;
    }

    public function getOrderItemIds()
    {
        return $this->orderItemIds;
    }

    public function getItemParameter($orderItemId)
    {
        return isset($this->items[$orderItemId]) ? $this->items[$orderItemId] : null;
    }
}
