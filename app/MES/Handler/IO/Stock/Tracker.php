<?php

namespace App\MES\Handler\IO\Stock;

use ArrayIterator;
use App\Models\Service\ModelBuilder\SourceParameter;
use App\Models\SourceConfig;
use App\Models\Service\Model\Stock\StockItem;
use Traversable;
use WMGCore\Services\ConfigService;
use Carbon\Carbon;

/**
 * Stock source tracker
 *
 *
 * @category WMG
 * @package  App\MES\Handler\IO\Stock
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Tracker implements \IteratorAggregate
{
    /**
     * MES stock file columns
     */
    const FIELD_SKU                   = 'catalogue_item_bar_code';
    const FIELD_AVAILABLE_STOCK_VALUE = 'available_quantity';
    const FIELD_ALLOCATED__STOCK_VALUE = 'allocated_quantity';
    const FIELD_SOURCE_ID             = 'distribution_center_number';
    const FIELD_ARTIST_NAME            = 'main_participant_name';
    const STOCK_BATCH_SIZE_PATH       = 'stock.batch.size';
    const STOCK_DEFAULT_BATCH_SIZE    = 1000;
    const BATCH_PROCESS_HASH_ALGO = 'md5';
    /** @var SourceParameter[] */
    protected $sources;
    protected $sourceStockLines;
    protected $sourceMapping;
    private $configService;

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
    }

    /**
     * getSourceId
     * @param string $distributionId
     * @return mixed|null
     */
    protected function getSourceId(string $distributionId)
    {
        if (empty($this->sourceMapping)) {
            $sourceConfigs = SourceConfig::all();

            foreach ($sourceConfigs as $source) {
                $this->sourceMapping[$source->distribution_id] = $source->source_id;
            }
        }

        //get mcom source Id
        if (!empty($this->sourceMapping)) {
            if (array_key_exists($distributionId, $this->sourceMapping)) {
                return $this->sourceMapping[$distributionId];
            }
        }

        return $distributionId;
    }


    /**
     * addSourceStock
     * @param $stockData
     */
    public function addSourceStock($stockData)
    {

        $distributionId = trim($stockData[self::FIELD_SOURCE_ID]);
        //using mapping get mcom source id
        $sourceId = $this->getSourceId($distributionId);

        //log and skip if source Id doesnt exist
        if (empty($sourceId)) {
            //log
            return;
        }

        //extract required values from file

        $sku = trim($stockData[self::FIELD_SKU]);
        $quantity = floatval($stockData[self::FIELD_AVAILABLE_STOCK_VALUE])
                    - floatval($stockData[self::FIELD_ALLOCATED__STOCK_VALUE]);
        $quantity = $quantity < 0 ? 0 : $quantity;
        $allocatedQuantity = floatval($stockData[self::FIELD_ALLOCATED__STOCK_VALUE]);

        $artistName = ($stockData[self::FIELD_ARTIST_NAME]) ? trim($stockData[self::FIELD_ARTIST_NAME]) : '';

        if (!empty($sku)) {
            if (!isset($this->sourceStockLines[$sourceId])) {
                $this->sourceStockLines[$sourceId] = [];
            }

            if (!isset($this->sourceStockLines[$sourceId][$sku])) {
                $stockItem = new StockItem();
                $stockItem->sku = $sku;
                $stockItem->quantity = 0;
                $stockItem->allocatedQuantity = 0;
                $stockItem->artistName = $artistName;
                $this->sourceStockLines[$sourceId][$sku] = $stockItem;
            }
            $this->sourceStockLines[$sourceId][$sku]->quantity += $quantity;
            $this->sourceStockLines[$sourceId][$sku]->allocatedQuantity += $allocatedQuantity;
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

    /**
     * @return mixed|null
     */
    private function getBatchSize()
    {
        return $this->configService->get(
            self::STOCK_BATCH_SIZE_PATH,
            self::STOCK_DEFAULT_BATCH_SIZE
        );
    }
}
