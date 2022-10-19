<?php

namespace App\IM\Handler\IO;

use App\Exceptions\NoRecordException;
use App\Stock\Handler\IO\Tracker;

/**
 * Class ApiStock
 * Import stock from Ingram Micro Warehouse API
 *
 * @category WMG
 * @package  App\IM\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ApiStock extends ApiAbstract
{
    /**
     * SKU field in API stock response object
     */
    const STOCK_LINE_SKU_INDEX = 'SKU';

    /**
     * Available Quantity field in API stock response object
     */
    const STOCK_LINE_QUANTITY_INDEX = 'QuantityAvailable';


    const SOURCE_ID = 'EU';

    const NO_RECORDS_MESSAGE = "No Stock returned from Ingram Micro stock Api";

    /**
     * @var string
     */
    protected $apiURI = 'rest/v1/Stock';

    protected $apiName = "Ingram Micro Stock Import";

    /**
     * Use to keep track of stock updates for later processing
     * @var Tracker
     */
    protected $sourceTracker;

    /**
     * @inheritdoc
     */
    public function start(array $data = null)
    {
        parent::start($data);

        //Setup stock update tracker, used to group stock by source
        $this->sourceTracker = app()->make(Tracker::class);
        $this->sourceTracker->reset();
    }

    /**
     * @inheritdoc
     *
     */
    public function receive($callback)
    {
        //Get stock data from IM Stock API
        $stockLines = $this->getDataFromWarehouse();

        //skip if there are no updates
        if (!is_array($stockLines) || empty($stockLines)) {
            throw new NoRecordException(self::NO_RECORDS_MESSAGE);
        }

        //iterate through stock updates and track for later processing
        foreach ($stockLines as $stockLine) {
            //validate expected values
            if (!isset($stockLine[self::STOCK_LINE_SKU_INDEX]) ||
                !array_key_exists(self::STOCK_LINE_QUANTITY_INDEX, $stockLine)
            ) {
                continue;
            }

            //add to tracker
            $this->sourceTracker->addSourceStock(
                $stockLine[self::STOCK_LINE_SKU_INDEX],
                $stockLine[self::STOCK_LINE_QUANTITY_INDEX],
                self::SOURCE_ID
            );
        }

        $this->onFinishReceivingData($callback);
    }


    /**
     * After receiving the data from API, send it to the Stock handler
     *
     * @param $callback
     */
    protected function onFinishReceivingData($callback)
    {
        $this->sourceTracker->buildSources();

        //append batch info to source if required.
        $this->sourceTracker->batchStockUpdates();

        foreach ($this->sourceTracker->getIterator() as $sourceStockModel) {
            /**@var App\Models\Service\ModelBuilder\SourceParameter $sourceStockModel*/
            call_user_func($callback, $sourceStockModel);
        }
    }

    public function send($data, $callback = null)
    {
        // TODO: Implement send() method.
    }

    public function finish(array $data = null)
    {
        // TODO: Implement finish() method.
    }

    public function rollback(...$args)
    {
        // TODO: Implement rollback() method.
    }
}
