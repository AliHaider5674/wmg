<?php
namespace App\Mdc\Service\Event\ClientHandler;

use App\Mdc\Constants\ConfigConstant;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Core\Services\EventService;
use App\Models\StockItem;
use WMGCore\Services\ConfigService;

/**
 * Handle Stock Update
 *
 * Class StockIO
 * @category WMG
 * @package  App\Mdc\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class StockHandler extends HandlerAbstract
{

    protected $handEvents = [
        EventService::EVENT_SOURCE_UPDATE
    ];

    private $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Handle request
     *
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param \SoapClient $client
     *
     * @return array
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        /** @var \App\Models\Service\Model\Stock $stock */
        /** @var \App\Models\Service\Model\Stock\StockItem  $stockItem */
        $stock = $request->getData();
        $stocks = $stock->snapshot->stock;

        $ignoreSkus = [];

        if (!empty($this->getStockSourceIds())
            && !in_array(strtoupper($stock->snapshot->sourceId), $this->getStockSourceIds())
        ) {
            return ;
        } elseif (!empty($this->getStockSourceIds()) && !$this->isAllowedSourceOverlap()) {
            $skus = [];
            foreach ($stocks as $stockItem) {
                $skus[] = $stockItem->sku;
            }
            //Check if there are multiple source have this sku
            $ignoreSkus = StockItem::whereNotIn('source_id', $this->getStockSourceIds())
                ->whereIn('sku', $skus)
                ->pluck('sku', 'sku')
                ->toArray();
        }
        $requestData = [];
        foreach ($stocks as $stockItem) {
            if (array_key_exists($stockItem->sku, $ignoreSkus)) {
                continue;
            }
            $requestData[] = [
                'sku' => $stockItem->sku,
                'qty' => $stockItem->quantity,
            ];
        }
        if (empty($requestData)) {
            return ['message' => 'ignore'];
        }
        $result = $client->stockSetMulti($request->token, $requestData, strtoupper($stock->snapshot->sourceId));
        return $result;
    }

    /**
     * Source IDs that allow send ot MDC
     *
     * @return mixed|null
     */
    private function getStockSourceIds()
    {
        return $this->configService->getJson(ConfigConstant::MDC_STOCK_SOURCE_IDS, []);
    }

    /**
     * If allowed a stock has other source id other than the ones in
     * getStockSourceIds
     *
     * @return int
     */
    private function isAllowedSourceOverlap()
    {
        return intval($this->configService->get(ConfigConstant::MDC_ALLOW_STOCK_SOURCE_OVERLAP, 0));
    }
}
