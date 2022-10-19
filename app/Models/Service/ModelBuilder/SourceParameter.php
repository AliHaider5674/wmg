<?php
namespace App\Models\Service\ModelBuilder;

use App\MES\Handler\IO\Stock\BatchInfo;
use App\Models\Service\Model\Stock\StockItem;

/**
 * SourceParameter
 *
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class SourceParameter
{

    /**
     * MES stock file columns
     */
    const FIELD_SKU                   = 'catalogue_item_code';
    const FIELD_AVAILABLE_STOCK_VALUE = 'available_quantity';

    protected $sourceId;
    protected $sourceBatch;
    /** @var StockItem[] */
    public $stockLines;


    public function __construct($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    /**
     * addStockItemToSource
     * @param array $stockData
     */
    public function addStockItemToSource($stockData)
    {
        //extract required values from file

            $sku = trim($stockData[self::FIELD_SKU]);
            $quantity = (float)$stockData[self::FIELD_AVAILABLE_STOCK_VALUE];

        if (!empty($sku)) {
            $stockItem      = new StockItem();
            $stockItem->sku = $sku;
            $stockItem->quantity = $quantity;
            $this->stockLines[] = $stockItem;
        }
    }


    /**
     * addStockItemToSource
     * @param array $stockData
     */
    public function addBatchInfoToSource($batchData)
    {
        //extract required values from file
        $this->sourceBatch                = new BatchInfo();
        $this->sourceBatch->processId     = $batchData['processId'];
        $this->sourceBatch->processNumber = $batchData['processNumber'];
        $this->sourceBatch->processTotal  = $batchData['processTotal'];
    }

    /**
     * getBatchInfo
     * @return mixed
     */
    public function getBatchInfo()
    {
        return $this->sourceBatch;
    }

    /**
     * getSourceId (Warehouse Id)
     * @return mixed
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * getSkuCount
     * return number of stock lines for source to import
     * @return int|void
     */
    public function getSkuCount()
    {
        return count($this->stockLines);
    }

    public function setStockLines(array $stockLines)
    {
        $this->stockLines = $stockLines;
    }
}
