<?php

namespace App\Models\Service\Model\Stock;

use App\Models\Service\Model\Serialize;

/**
 * StockItem
 *
 * @category App\MES\Handler\IO\Stock
 * @package  App\MES\Handler\IO\Stock
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class StockItem extends Serialize
{
    public $sku;
    public $quantity;
    public $unlimited = 0;
    public $artistName = '';
    public $allocatedQuantity;
}
