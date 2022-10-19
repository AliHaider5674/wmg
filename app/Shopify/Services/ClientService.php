<?php

namespace App\Shopify\Services;

use App\Core\Repositories\ServiceRepository;
use App\Models\Service;
use App\Shopify\Clients\ShopifySDK;
use App\Shopify\Models\ShopifyOrder;

/**
 * Class ClientService
 * @package App\Shopify\Services
 *
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ClientService
{
    private $serviceMapCache;
    private ServiceRepository $serviceRepository;
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function getClient(Service $service) : ShopifySDK
    {
        $addition = $service->getAddition();
        $url = $service->app_url ?? $addition['shop_url'];
        return new ShopifySDK([
            'ShopUrl' => $url,
            'ApiKey' => $addition['api_key'],
            'Password' => $addition['password']
        ]);
    }

    public function getClientByOrder(ShopifyOrder $order) : ShopifySDK
    {
        return $this->getClient($this->getService($order->service_id));
    }

    private function getService($serviceId)
    {
        if (!isset($this->serviceMapCache)) {
            foreach ($this->serviceRepository->allService() as $service) {
                $this->serviceMapCache[$service->id] = $service;
            }
        }
        return $this->serviceMapCache[$serviceId];
    }
}
