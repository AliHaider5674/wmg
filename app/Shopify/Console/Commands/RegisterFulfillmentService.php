<?php

namespace App\Shopify\Console\Commands;

use App\Core\Enums\ServiceStatus;
use App\Models\Service;
use App\Shopify\Constants\ConfigConstant;
use App\Shopify\Constants\RouteConstant;
use App\Shopify\Enums\ShopifyClient;
use App\Shopify\Repositories\ShopifyFSRRepository;
use App\Shopify\Services\ClientService;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Core\Models\Warehouse;
use App\Core\Enums\WarehouseStatus;
use WMGCore\Services\ConfigService;

/**
 * Class RegisterWebhooks
 * @package App\Shopify\Console\Commands
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegisterFulfillmentService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:fulfillment:register';
    protected $description = 'Register fulfillment service with Shopify. ' .
                             'This required Fulfillment microservice to have shopify service first.';
    private ShopifyFSRRepository $shopifyFSRRepository;
    private ClientService $clientService;

    /**
     * @param ShopifyFSRRepository $shopifyFSRRepository
     * @param ClientService        $clientService
     */
    public function __construct(
        ShopifyFSRRepository $shopifyFSRRepository,
        ClientService $clientService
    ) {
        parent::__construct();
        $this->addOption(
            'region',
            'r',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'List of regions of warehouses that will connect to Shopify.'
        );
        $this->clientService = $clientService;
        $this->shopifyFSRRepository = $shopifyFSRRepository;
    }

    /**
     * @param Warehouse     $warehouses
     * @param ConfigService $configService
     * @param Service       $service
     */
    public function handle(
        Warehouse     $warehouses,
        ConfigService $configService,
        Service       $service
    ): void {
        try {
            $warehouseQuery = $warehouses::where('status', '=', WarehouseStatus::ACTIVE)
                ->whereIn('code', $configService->getJson(ConfigConstant::SUPPORTED_WAREHOUSES));
            $regions = $this->option('region');
            if (!empty($regions)) {
                $warehouseQuery->whereIn('region', $this->option('region'));
            }
            $registerWarehouses = $warehouseQuery->get();
            $services = $service::where('status', '=', ServiceStatus::ACTIVE)
                ->whereIn('client', [ShopifyClient::GRAPHQL, ShopifyClient::RESTFUL])->get();
            $this->register($services, $registerWarehouses);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param $services
     * @param $registerWarehouses
     * @throws \PHPShopify\Exception\ApiException
     * @throws \PHPShopify\Exception\CurlException
     */
    private function register($services, $registerWarehouses)
    {
        $existentMap = $this->getExistentRegistrationMap();
        foreach ($services as $service) {
            $client = $this->clientService->getClient($service);
            foreach ($registerWarehouses as $warehouse) {
                $key = $this->getRegistrationMapKey($service->id, $warehouse->id);
                $callback = route(
                    RouteConstant::FULFILLMENT_SERVICE_ROUTE_NAME,
                    [
                        'warehouse_code' => $warehouse->code,
                        'shop' => $service->app_id
                    ]
                );

                if (array_key_exists($key, $existentMap)) {
                    $serviceRegistration = $existentMap[$key];
                    $shopifyServiceId = $serviceRegistration->getAttribute('shopify_service_id');
                    $response = $client->FulfillmentService($shopifyServiceId)->put([
                        'name' => $warehouse->name,
                        'tracking_support' => true,
                        'inventory_management' => true,
                        'fulfillment_orders_opt_in' => true,
                        'callback_url' => $callback,
                        "format" => "json",
                        'permits_sku_sharing' => true
                    ]);

                    $this->shopifyFSRRepository->updateById($serviceRegistration->id, [
                        'warehouse_id' => $warehouse->id,
                        'service_id' => $service->id,
                        'shopify_service_id' => $response['id'],
                        'shopify_location_id' => $response['location_id']
                    ]);

                    $this->line(sprintf(
                        'Update service %s with warehouse %s.',
                        $service->name,
                        $warehouse->name,
                    ));
                    continue;
                }

                $response = $client->FulfillmentService->post([
                    'name' => $warehouse->name,
                    'tracking_support' => true,
                    'inventory_management' => true,
                    'fulfillment_orders_opt_in' => true,
                    'callback_url' => $callback,
                    "format" => "json",
                    'permits_sku_sharing' => true
                ]);

                $shopifyServiceId = $response['id'];
                $this->shopifyFSRRepository->create([
                    'warehouse_id' => $warehouse->id,
                    'service_id' => $service->id,
                    'shopify_service_id' => $response['id'],
                    'shopify_location_id' => $response['location_id']
                ]);
                $this->line(sprintf(
                    'Registered service %s with warehouse %s. Shopify service id: %s',
                    $service->name,
                    $warehouse->name,
                    $shopifyServiceId
                ));
            }
        }
    }

    /**
     * @return array
     */
    private function getExistentRegistrationMap()
    {
        $registrations = $this->shopifyFSRRepository->getAllRegistrations();
        $map = [];
        foreach ($registrations as $registration) {
            $key = $this->getRegistrationMapKey($registration['service_id'], $registration['warehouse_id']);
            $map[$key] = $registration;
        }
        return $map;
    }

    /**
     * @param $serviceId
     * @param $warehouseId
     * @return string
     */
    private function getRegistrationMapKey($serviceId, $warehouseId)
    {
        return  $serviceId . ',' . $warehouseId;
    }
}
