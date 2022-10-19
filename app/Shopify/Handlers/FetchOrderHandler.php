<?php


namespace App\Shopify\Handlers;

use App\Core\Handlers\HandlerInterface;
use App\Core\Repositories\ServiceRepository;
use App\Models\Service;
use App\Shopify\Handlers\FetchShipmentOrder\Processor as FulfillmentOrderProcessor;
use App\Shopify\Enums\ShopifyClient;
use App\Shopify\Factories\Shopify\FulfillmentOrderFactory;
use App\Shopify\Handlers\FetchShipmentOrder\FetchOrder;
use App\Shopify\Repositories\ShopifyFFetchLogRepository;
use App\Shopify\Repositories\ShopifyFSRRepository;
use App\Shopify\Services\ClientService;
use App\Shopify\Services\UrlService;
use App\Shopify\Handlers\FetchShipmentOrder\Processor;

/**
 * Class FetchOrderHandler
 * @todo break the logic by using IOAdapter
 */
class FetchOrderHandler implements HandlerInterface
{
    protected $config;
    private ClientService $clientService;
    private ServiceRepository $serviceRepository;
    private ShopifyFSRRepository $shopifyFSRRepository;
    private UrlService $urlService;
    private FulfillmentOrderProcessor $fulfillmentOrderProcessor;
    private FulfillmentOrderFactory $fulfillmentOrderFactory;
    private ShopifyFFetchLogRepository $shopifyFFetchLogRepository;
    private FetchOrder $fetchOrder;
    private Processor $processor;

    public function __construct(
        ClientService $clientService,
        ServiceRepository $serviceRepository,
        ShopifyFSRRepository $shopifyFSRRepository,
        UrlService $urlService,
        FulfillmentOrderProcessor $fulfillmentOrderProcessor,
        FulfillmentOrderFactory $fulfillmentOrderFactory,
        ShopifyFFetchLogRepository $shopifyFFetchLogRepository,
        FetchOrder $fetchOrder,
        Processor $processor
    ) {
        $this->clientService = $clientService;
        $this->serviceRepository = $serviceRepository;
        $this->shopifyFSRRepository = $shopifyFSRRepository;
        $this->urlService = $urlService;
        $this->fulfillmentOrderProcessor = $fulfillmentOrderProcessor;
        $this->fulfillmentOrderFactory = $fulfillmentOrderFactory;
        $this->shopifyFFetchLogRepository = $shopifyFFetchLogRepository;
        $this->fetchOrder = $fetchOrder;
        $this->processor = $processor;
    }

    public function validate(): bool
    {
        return true;
    }

    public function handle()
    {
        $services = $this->serviceRepository->getServiceByClient([ShopifyClient::RESTFUL, ShopifyClient::GRAPHQL]);
        foreach ($services as $service) {
            $this->fetchOrders($service);
        }
    }

    private function fetchOrders(Service $service)
    {
        $client = $this->clientService->getClient($service);
        $locations = $this->getLocations($service);
        $url = $client->AssignedShipmentOrder->generateUrl([]) .
            '?' . $this->urlService->getQuery([
                'assignment_status' => 'fulfillment_requested',
                'location_ids' => $locations
            ]);
        $response = $client->AssignedShipmentOrder->get([], $url);
        foreach ($response['fulfillment_orders'] as $shipmentOrder) {
            $this->fetchOrder->handler($client, $service, $shipmentOrder, $this->processor);
        }
    }

    /**
     * @param Service $service
     * @return Array
     */
    private function getLocations(Service $service) : Array
    {
        $locations = [];
        $registrations = $this->shopifyFSRRepository->getRegistrationByService($service);
        foreach ($registrations as $registration) {
            $locations[] = $registration->getAttribute('shopify_location_id');
        }
        return $locations;
    }
}
