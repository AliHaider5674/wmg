<?php
namespace App\Models\Service\ModelBuilder;

use App\Models\Service\Model\Stock;
use App\Models\Service\Model\Stock\Snapshot;
use App\Models\Service\Model\Stock\Batch;
use App\Models\Service\Model\Stock\StockItem;

/**
 * A builder that build
 * stock requests for external services
 *
 * Class StockBuilder
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class StockBuilder
{

    const STOCK_SNAPSHOT_MODE = "DELTA";

    /**
     * Build stock model that send to service endpoint
     *
     * @param \Illuminate\Database\Eloquent\Collection $stockItems
     * @return \App\Models\Service\Model\Stock
     */
    public function build($stockItems, $sourceId)
    {
        $stock = new Stock();

        $snapshot            = new Snapshot();
        $snapshot->sourceId  = $sourceId;
        $snapshot->mode      = self::STOCK_SNAPSHOT_MODE;
        $snapshot->createdOn = date('Y-m-d\TH:i:sP');

        $stockLines = [];
        foreach ($stockItems as $stockItem) {
            $item = new StockItem();
            $item->fill([
                'sku' => $stockItem->sku,
                'quantity' => $stockItem->qty,
                'unlimited' => false
            ]);
            $stockLines[] = $item;
        }

        $snapshot->stock     = $stockLines;
        $stock->snapshot     = $snapshot;

        return $stock;
    }

    /**
     * Build stock model that send to service endpoint
     *
     * @param \App\Models\Service\ModelBuilder\StockBuilder $parameter
     * @return \App\Models\Service\Model\Stock
     */
    public function buildFromSourceParameter(SourceParameter $parameter)
    {
        $stock = new Stock();

        $snapshot            = new Snapshot();
        $snapshot->sourceId  = $parameter->getSourceId();
        $snapshot->mode      = self::STOCK_SNAPSHOT_MODE;
        $snapshot->createdOn = date('Y-m-d\TH:i:sP');
        $snapshot->stock     = $parameter->stockLines;


        //append batchinfo if exists
        if (!empty($parameter->getBatchInfo())) {
            $snapshot->batch = new Batch($parameter->getBatchInfo());
        }

        $stock->snapshot     = $snapshot;

        return $stock;
    }
}
