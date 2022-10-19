<?php
namespace App\Models\Service\Model;

use App\Models\Service\Model\Stock\Snapshot;

/**
 * This is external service request model container for stock
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
class Stock extends Serialize
{
    /** @var Snapshot */
    public $snapshot;

    /**
     * Create Snapshot
     *
     * @return \App\Models\Service\Model\Stock\Snapshot
     */
    public function newSnapshot()
    {
        $this->snapshot = new Snapshot();
        return $this->snapshot;
    }
}
