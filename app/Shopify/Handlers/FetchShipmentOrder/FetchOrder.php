<?php

namespace App\Shopify\Handlers\FetchShipmentOrder;

use App\Core\Exceptions\Mutators\ValidationException;
use App\DataMapper\Exceptions\InvalidMappingException;
use App\Shopify\Enums\ShopifyFulfillmentFetchStatus;
use App\Shopify\Factories\Shopify\FulfillmentOrderFactory;
use App\Shopify\Handlers\FetchShipmentOrder\Processor as FulfillmentOrderProcessor;
use App\Shopify\Models\ShopifyFailedFulfillmentOrder;
use App\Shopify\Repositories\ShopifyFFetchLogRepository;
use PHPShopify\Exception\ApiException;
use PHPShopify\Exception\CurlException;

/**
 * Class ShipmentOrderService
 * @package App\Shopify\Handlers\FetchShipmentOrder
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class FetchOrder
{
    private FulfillmentOrderProcessor $fulfillmentOrderProcessor;
    private FulfillmentOrderFactory $fulfillmentOrderFactory;
    private ShopifyFFetchLogRepository $shopifyFFetchLogRepository;

    public function __construct(
        FulfillmentOrderProcessor $fulfillmentOrderProcessor,
        FulfillmentOrderFactory $fulfillmentOrderFactory,
        ShopifyFFetchLogRepository $shopifyFFetchLogRepository
    ) {
        $this->fulfillmentOrderProcessor = $fulfillmentOrderProcessor;
        $this->fulfillmentOrderFactory = $fulfillmentOrderFactory;
        $this->shopifyFFetchLogRepository = $shopifyFFetchLogRepository;
    }

    /**
     * process
     * @param $client
     * @param $service
     * @param $shipmentOrder
     * @param Processor $processor
     * @throws CurlException
     * @throws InvalidMappingException
     */
    public function handler($client, $service, $shipmentOrder, Processor $processor)
    {
        $fulfillmentOrder = $this->fulfillmentOrderFactory->createFromUnderScore($shipmentOrder);
        try {
            $order = $processor->process(
                $fulfillmentOrder,
                $client,
                $service->getAttribute('app_id'),
                $service->getAttribute('name'),
                $service->id
            );

            if (!$order) {
                $this->shopifyFFetchLogRepository->addLog(
                    $shipmentOrder['id'],
                    ShopifyFulfillmentFetchStatus::SKIPPED,
                    sprintf('skip order %s', $fulfillmentOrder->orderId)
                );
                return;
            }
            $this->shopifyFFetchLogRepository->addLog(
                $shipmentOrder['id'],
                ShopifyFulfillmentFetchStatus::SUCCESS,
                'Convert to order ' . $order->getAttribute('id')
            );

            $processor->handlePostSave($order);
        } catch (ValidationException | ApiException $e) {
            $this->shopifyFFetchLogRepository->addLog(
                $shipmentOrder['id'],
                ShopifyFulfillmentFetchStatus::ERROR,
                $e->getMessage()
            );
        }
    }
}
