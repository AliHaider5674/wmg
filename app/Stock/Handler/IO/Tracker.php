<?php

namespace App\Stock\Handler\IO;

use App\Models\Service\ModelBuilder\SourceParameter;
use App\Models\SourceConfig;
use App\Models\Service\Model\Stock\StockItem;
use ArrayIterator;
use Traversable;
use WMGCore\Services\ConfigService;
use Carbon\Carbon;

/**
 * Stock source tracker
 *
 * @category WMG
 * @package  App\Stock\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class Tracker implements \IteratorAggregate
{
    const STOCK_BATCH_SIZE_PATH       = 'stock.batch.size';
    const STOCK_DEFAULT_BATCH_SIZE    = 500;
    const BATCH_PROCESS_HASH_ALGO = 'md5';

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

        /**
         * @todo move configuration to fufillment.configurations table
         */
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
     * Track and aggregate stock update
     *
     * @param string    $sku        stock sku
     * @param int|float $quantity   available stock quantity
     * @param string    $sourceId   warehouse Identifier
     */
    public function addSourceStock(string $sku, $quantity, string $sourceId)
    {
        //group stock updates by sources (warehouses)
        if (!isset($this->sourceStockLines[$sourceId])) {
            $this->sourceStockLines[$sourceId] = [];
        }

        //track stock updates by sources
        if (!isset($this->sourceStockLines[$sourceId][$sku])) {
            $stockItem = new StockItem();
            $stockItem->sku = $sku;
            $stockItem->quantity = 0;
            $this->sourceStockLines[$sourceId][$sku] = $stockItem;
        }

        $this->sourceStockLines[$sourceId][$sku]->quantity += $quantity;
    }

    /**
     * Package stock updates into batches for each warehouse
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
