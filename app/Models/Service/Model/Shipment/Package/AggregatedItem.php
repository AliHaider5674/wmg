<?php
namespace App\Models\Service\Model\Shipment\Package;

use App\Models\Service\Model\Serialize;

/**
 * Aggregated items
 *
 * Class AggregatedItemModel
 * @category WMG
 * @package  App\Models\Service\Model\Shipment\Package
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AggregatedItem extends Serialize
{
    /** @var string */
    public $aggregatedLineId;
    /** @var string */
    public $sku;
    /** @var integer */
    public $quantity;
    /** @var [] */
    public $orderLines = []; //Reference to the order lines from requested shipment

    public function addOrderLines($orderLine)
    {
        $this->orderLines[] = $orderLine;
    }

    public function addHiddenLineQtyMap($lineId, $qty)
    {
        $map = $this->getHiddenData('line_qty_map');
        if ($map === null) {
            $map = [];
        }
        $map[] = [
            'order_item_id' => $lineId,
            'qty' => $qty
        ];
        $this->setHiddenData('line_qty_map', $map);
    }

    public function getHiddenLineQtyMap()
    {
        return $this->getHiddenData('line_qty_map');
    }
}
