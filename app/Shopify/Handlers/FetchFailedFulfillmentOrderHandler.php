<?php

namespace App\Shopify\Handlers;

use App\Core\Handlers\HandlerInterface;
use App\DataMapper\Exceptions\InvalidMappingException;
use App\Shopify\Handlers\FetchShipmentOrder\FailOrderProcessor;
use App\Shopify\Models\ShopifyFailedFulfillmentOrder;
use App\Core\Repositories\ServiceRepository;
use App\Shopify\Services\ClientService;
use App\Shopify\Factories\Shopify\FulfillmentOrderFactory;
use App\Shopify\Handlers\FetchShipmentOrder\FetchOrder;
use App\Shopify\Structures\Shopify\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PHPShopify\Exception\ApiException;
use PHPShopify\Exception\CurlException;
use App\Models\Order as SalesOrder;

/**
 * Class FetchFailedFulfillmentOrderHandler
 * @package App\Shopify\Handlers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class FetchFailedFulfillmentOrderHandler implements HandlerInterface
{

    const MAX_RETRIES = 3;
    const MAX_QUERY_LIMIT = 50;
    private ClientService $clientService;
    private ServiceRepository $serviceRepository;
    private FulfillmentOrderFactory $fulfillmentOrderFactory;
    private FetchOrder $fetchOrder;
    private FailOrderProcessor $processor;
    private array $allowedStatuses = array ('accepted');

    public function __construct(
        ClientService $clientService,
        ServiceRepository $serviceRepository,
        FulfillmentOrderFactory $fulfillmentOrderFactory,
        FailOrderProcessor $processor,
        FetchOrder $fetchOrder
    ) {
        $this->clientService = $clientService;
        $this->serviceRepository = $serviceRepository;
        $this->fulfillmentOrderFactory = $fulfillmentOrderFactory;
        $this->fetchOrder = $fetchOrder;
        $this->processor = $processor;
    }

    /**
     * handle
     * @throws ApiException
     * @throws CurlException
     */
    public function handle()
    {
        //get service id of eligible fulfillment orders
        $serviceIds = $this->getServiceIds();

        //iterate through services
        foreach ($serviceIds as $serviceId) {
            $service = $this->serviceRepository->getById($serviceId);
            $this->processFailedFulfillmentOrders($service);
        }
    }

    /**
     * getServiceIds
     * @return Collection
     */
    public function getServiceIds(): Collection
    {
        return ShopifyFailedFulfillmentOrder::query()
            ->where('attempts', '<=', self::MAX_RETRIES)
            ->groupBy('service_id')
            ->pluck('service_id');
    }

    /**
     * @throws ApiException
     * @throws CurlException
     * @throws InvalidMappingException
     */
    public function processFailedFulfillmentOrders($service)
    {
        $client = $this->clientService->getClient($service);

        //get eligible fulfillment orders for service
        foreach ($this->getFulfillmentOrdersByService($service) as $failedFulfillmentOrder) {
            //get fulfillment order
            $shipmentOrder = $client->FulfillmentOrder($failedFulfillmentOrder->fulfillment_order_id)->get();

            if (!$this->isValid($shipmentOrder)) {
                continue;
            }

            $this->fetchOrder->handler($client, $service, $shipmentOrder, $this->processor);
        }
    }

    /**
     * removeFailedFulfillmentOrder
     * @param int $fulfillmentOrderId
     */
    public function removeFailedFulfillmentOrder(int $fulfillmentOrderId)
    {
        ShopifyFailedFulfillmentOrder::where('fulfillment_order_id', $fulfillmentOrderId)->delete();
    }

    public function isValid($shipmentOrder) : bool
    {
        //check order does not already exist
        $count = SalesOrder::where('request_id', $shipmentOrder['id'])->count();

        if ($count) {
            $this->removeFailedFulfillmentOrder($shipmentOrder['id']);
            return false;
        }

        //check shipmentOrder is in require state
        if (!in_array($shipmentOrder['request_status'], $this->allowedStatuses)) {
            $this->removeFailedFulfillmentOrder($shipmentOrder['id']);
            return false;
        }
        return true;
    }

    /**
     * getFulfillmentOrdersByService
     * @param $service
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getFulfillmentOrdersByService($service)
    {
        return ShopifyFailedFulfillmentOrder::query()
            ->select(['id', 'fulfillment_order_id'])
            ->where('service_id', '=', $service->id)
            ->where('attempts', '<=', self::MAX_RETRIES)
            ->limit(self::MAX_QUERY_LIMIT)->get();
    }

    public function validate(): bool
    {
        return true;
    }
}
