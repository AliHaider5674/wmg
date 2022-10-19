<?php

namespace App\Salesforce\ServiceClients\Handlers;

use App\Core\Services\EventService;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Models\Service\Model\Stock;
use App\Models\Service\Model\Stock\StockItem;
use App\Salesforce\Clients\SalesforceSDK;
use App\Salesforce\ServiceClients\Handlers\Stock\BatchStockItem;
use GuzzleHttp\Exception\GuzzleException;
use WMGCore\Services\ConfigService;

/**
 * Class StockHandler
 * @package App\Salesforce\ServiceClients\Handlers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class StockHandler extends HandlerAbstract
{
    const SALESFORCE_STOCK_SOURCE_IDS = 'salesforce.source_ids';
    const SALESFORCE_STOCK_RECORD_MAX_SIZE = 'salesforce.stock_record_max_size';
    const SALESFORCE_STOCK_RECORD_MAX_SIZE_DEFAULT = 100;

    protected $handEvents = [
        EventService::EVENT_SOURCE_UPDATE
    ];

    private ConfigService $configService;

    public function __construct(
        ConfigService $configService
    ) {
        $this->configService = $configService;
    }


    /**
     * handle
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param SalesforceSDK $client
     * @throws GuzzleException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        /**
         * @var $data Stock
         */
        $data = $request->getData();
        $sourceId = $data->snapshot->sourceId;

        //check if stock updates are allowed for source/warehouse check configuration
        if (!$this->isSourceIdAllowed($sourceId)) {
            return;
        }

        //skip if no stock to update
        $stock = $data->snapshot->stock;


        if (empty($stock)) {
            return;
        }

        $recordMaxSize = $this->configService
            ->getJson(self::SALESFORCE_STOCK_RECORD_MAX_SIZE, self::SALESFORCE_STOCK_RECORD_MAX_SIZE_DEFAULT);
        $chunkedStock = array_chunk($stock, $recordMaxSize);

        foreach ($chunkedStock as $stockItems) {
            $postData = array();
            //build payload
            $batchItems = $this->getBatchItems($stockItems, $sourceId);

            if (empty($batchItems)) {
                continue;
            }
            $postData['records'] = $batchItems;

            $client->batchInventoryUpdate($postData);
        }
    }

    /**
     * getBatchItems
     * @param array $stockItems
     * @param string $sourceId
     * @return array
     */
    public function getBatchItems(array $stockItems, string $sourceId): array
    {
        $batchItems = array();
        /**
         * @var $stockItem StockItem
         */
        foreach ($stockItems as $stockItem) {
            $batchItem = new BatchStockItem($stockItem->sku);
            $batchItem->setLocation($sourceId)
                ->setOnHand($stockItem->quantity);

            $batchItems[] = $batchItem;
        }

        return $batchItems;
    }


    /**
     * check if stock updates are allowed for source/warehouse
     * isSourceIdAllowed
     */
    protected function isSourceIdAllowed(string $sourceId) : bool
    {
        $allowedSourceIds = $this->configService->getJson(self::SALESFORCE_STOCK_SOURCE_IDS, []);

        if (!empty($allowedSourceIds)
            && !in_array(strtoupper($sourceId), $allowedSourceIds)
        ) {
            return false;
        }

        return true;
    }
}
