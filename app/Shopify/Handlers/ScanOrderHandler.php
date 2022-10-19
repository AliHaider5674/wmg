<?php


namespace App\Shopify\Handlers;

use App\Core\Handlers\HandlerInterface;
use App\Core\Repositories\ServiceDataRepository;
use App\Core\Repositories\ServiceRepository;
use App\Core\Repositories\WarehouseRepository;
use App\Models\Service;
use App\Shopify\Constants\ConfigConstant;
use App\Shopify\Enums\ShopifyClient;
use App\Shopify\Repositories\ShopifyOrderRepository;
use App\Shopify\Services\ClientService;
use Illuminate\Support\Carbon;
use Exception;

/**
 * ScanOrderHandler
 * @todo break the logic by using IOAdapter
 */
class ScanOrderHandler implements HandlerInterface
{
    const SERVICE_DATA_LAST_FETCH_ORDER = 'shopify.order.scan.last.order_id';
    protected array $config;
    protected array $supportedWarehouses;
    private ClientService $clientService;
    private ServiceRepository $serviceRepository;
    private ServiceDataRepository $serviceDataRepository;
    private ShopifyOrderRepository $shopifyRawOrderRepository;
    private WarehouseRepository $warehouseRepository;
    public function __construct(
        ClientService          $clientService,
        ServiceRepository      $serviceRepository,
        ServiceDataRepository  $serviceDataRepository,
        ShopifyOrderRepository $shopifyRawOrderRepository,
        WarehouseRepository    $warehouseRepository,
        $config
    ) {
        $this->clientService = $clientService;
        $this->serviceRepository = $serviceRepository;
        $this->serviceDataRepository = $serviceDataRepository;
        $this->shopifyRawOrderRepository = $shopifyRawOrderRepository;
        $this->warehouseRepository = $warehouseRepository;
        $supportedWarehouseCodes = $config[ConfigConstant::SUPPORTED_WAREHOUSES];
        $supportWarehouses = $this->warehouseRepository->getWarehousesByCodes($supportedWarehouseCodes);
        $this->supportedWarehouses = [];
        foreach ($supportWarehouses as $warehouse) {
            $this->supportedWarehouses[strtolower($warehouse->name)] = true;
        }
    }

    public function validate()
    {
        return true;
    }

    public function handle()
    {
        $services = $this->serviceRepository->getServiceByClient([ShopifyClient::RESTFUL, ShopifyClient::GRAPHQL]);
        foreach ($services as $service) {
            $this->scanOrders($service);
        }
    }

    private function scanOrders(Service $service) : void
    {

        $client = $this->clientService->getClient($service);
        $params = [
            'fulfillment_status' => 'unfulfilled',
            'financial_status' => 'paid',
            'limit' => 250
        ];
        $lastFetchedId = $this->serviceDataRepository->getServiceData($service, self::SERVICE_DATA_LAST_FETCH_ORDER);
        if ($lastFetchedId) {
            $params['since_id'] = $lastFetchedId->value;
        }

        $orders = $client->Order->get($params);
        $maxOrderId = 0;
        $processed = [];
        try {
            foreach ($orders as $order) {
                $orderId = $order['id'];
                $maxOrderId = max($maxOrderId, $orderId);
                if (!$this->isSupportedOrder($order)) {
                    continue;
                }
                $orderTime = Carbon::make($order['created_at']);
                $rawOrder = $this->shopifyRawOrderRepository->updateOrCreate([
                    'service_id' => $service->id,
                    'data' => json_encode($order),
                    'order_id' => $orderId,
                    'ordered_at' => $orderTime->format('Y-m-d H:i:s')
                ]);
                $processed[] = $rawOrder;
            }

            if ($maxOrderId === 0) {
                return;
            }

            $this->serviceDataRepository->updateServiceData($service, self::SERVICE_DATA_LAST_FETCH_ORDER, $maxOrderId);
        } catch (Exception $e) {
            foreach ($processed as $item) {
                $item->delete();
            }
            throw $e;
        }
    }

    private function isSupportedOrder($order) : bool
    {
        $items = $order['line_items'];
        foreach ($items as $item) {
            $warehouse = strtolower($item['fulfillment_service']);
            if (array_key_exists($warehouse, $this->supportedWarehouses) && $item['fulfillable_quantity'] > 0) {
                return true;
            }
        }
        return false;
    }
}
