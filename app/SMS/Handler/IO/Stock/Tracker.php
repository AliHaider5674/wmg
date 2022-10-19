<?php

namespace App\SMS\Handler\IO\Stock;

use App\Models\Service\ModelBuilder\SourceParameter;
use App\Models\Service\Model\Stock\StockItem;
use ArrayIterator;
use Traversable;
use WMGCore\Services\ConfigService;
use Carbon\Carbon;

/**
 * Stock source tracker
 *
 *
 * @category WMG
 * @package  App\SMS\Handler\IO\Stock
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Tracker implements \IteratorAggregate
{
    /**
     * MES stock file columns
     */
    const FIELD_SKU                   = 'Merch_Item_UPC_Unformated';
    const FIELD_AVAILABLE_STOCK_VALUE = 'Available_Inventory';
    const FIELD_SOURCE_ID             = 'Warehouse_Number';
    const FIELD_ARTIST_NAME            = 'Artist_Name';



    const STOCK_BATCH_SIZE_PATH       = 'stock.batch.size';
    const STOCK_DEFAULT_BATCH_SIZE    = 1000;
    const BATCH_PROCESS_HASH_ALGO = 'md5';
    protected $sources;
    protected $sourceStockLines;
    private $configService;
    private array $artists = array();


    /** @var Carbon */
    protected $currentTime;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function reset()
    {
        $this->sources = [];
        $this->currentTime = new Carbon(null, 'UTC');

        $this->artists = array();
    }


    /**
     * addSourceStock
     * @param $stockData
     */
    public function addSourceStock($stockData)
    {
        $sourceId = $stockData[self::FIELD_SOURCE_ID];
        //log and skip if source Id doesnt exist
        if (empty($sourceId)) {
            //log
            return;
        }

        //extract required values from file

        $sku = trim($stockData[self::FIELD_SKU]);
        $quantity = (float)$stockData[self::FIELD_AVAILABLE_STOCK_VALUE];

        $artistName = ($stockData[self::FIELD_ARTIST_NAME]) ? trim($stockData[self::FIELD_ARTIST_NAME]) : '';

        if (!in_array($artistName, $this->artists)) {
            $this->artists[] = $artistName;
        }

        if (!empty($sku)) {
            if (!isset($this->sourceStockLines[$sourceId])) {
                $this->sourceStockLines[$sourceId] = [];
            }

            if (!isset($this->sourceStockLines[$sourceId][$sku])) {
                $stockItem = new StockItem();
                $stockItem->sku = $sku;
                $stockItem->quantity = 0;
                $stockItem->artistName = $artistName;

                $this->sourceStockLines[$sourceId][$sku] = $stockItem;
            }
            $this->sourceStockLines[$sourceId][$sku]->quantity += $quantity;
        }
    }

    /**
     * buildSources
     *
     */
    public function buildSources()
    {
        //skip if there are no sources

        //iterate through sources and divide stock lines accordingly by limit
        foreach ($this->sourceStockLines as $sourceId => $sourceStockLine) {
            if (count($sourceStockLine) > $this->getBatchSize()) {
                //divide sku into batches

                $chunks = array_chunk(array_values($sourceStockLine), $this->getBatchSize());

                foreach ($chunks as $chunk) {
                    $model = new SourceParameter($sourceId);
                    $model->setStockLines($chunk);

                    $this->sources[] = $model;
                }
                continue;
            }

            $model = new SourceParameter($sourceId);
            $model->setStockLines(array_values($sourceStockLine));
            $this->sources[] = $model;
        }

        //reset stocklines
        $this->sourceStockLines = null;
    }


    /**
     * getSourceCount
     *
     * Get number of sources to update
     * @return int|void
     */
    public function getSourceCount()
    {
        return count($this->sources);
    }

    /**
     * getSourceCount
     *
     *
     * Get number of sources to update
     * @return int|void
     */
    public function getSourceStockCount()
    {
        return count($this->sourceStockLines);
    }

    /**
     * getTotalSkuCount
     * @return int
     */
    public function getTotalSkuCount()
    {

        $totalSkus = 0;
        foreach ($this->sourceStockLines as $stockLine) {
            $totalSkus += count($stockLine);
        }

        return $totalSkus;
    }

    /**
     * Return Batch Process Id
     *
     * generateBatchProcessId
     * @return string
     */
    protected function generateBatchProcessId()
    {
        return hash(
            self::BATCH_PROCESS_HASH_ALGO,
            config('mes.stock.batch-process-id-key') . time()
        );
    }

    public function batchStockUpdates()
    {
        //only need to batch if there is more than one source to update
        if ($this->getSourceCount() <= 1) {
            return;
        }

        $batchInfo = [
            //generate batch process id
            'processId' => $this->generateBatchProcessId(),
            //generate total batch number
            'processTotal' => $this->getSourceCount(),
            //iterate through collection
            //generate process number
            //inject batch process id, total batch number and process number into each source model
            'processNumber' => 0,
        ];

        foreach ($this->sources as $source) {
            $batchInfo['processNumber']++;
            $source->addBatchInfoToSource($batchInfo);
        }
    }

    /**
     * Description here
     *
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->sources);
    }

    private function getBatchSize()
    {
        return $this->configService->get(
            self::STOCK_BATCH_SIZE_PATH,
            self::STOCK_DEFAULT_BATCH_SIZE
        );
    }
}
