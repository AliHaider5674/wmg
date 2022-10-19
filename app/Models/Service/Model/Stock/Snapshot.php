<?php
namespace App\Models\Service\Model\Stock;

use App\Models\Service\Model\Serialize;

/**
 * Represents Stock update API payload
 *
 * https://omsdocs.magento.com/en/specifications/#magento.inventory.source_stock_management
 *
 * Class Stock
 * @category WMG
 * @package  App\Models\Service\Model
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Snapshot extends Serialize
{
    public $sourceId;
    public $mode;
    public $createdOn;
    /** @var [\App\Models\Service\Model\Stock\StockItem]*/
    public $stock = [];
    public $batch;

    public function newStockItem($sku, $qty, $unlimited = 0)
    {
        $newItem =  new StockItem();
        $newItem->sku = $sku;
        $newItem->quantity = $qty;
        $newItem->unlimited = $unlimited;
        $this->stock[] = $newItem;
        return $newItem;
    }
}
