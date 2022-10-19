<?php declare(strict_types=1);

namespace App\Shopify\Repositories;

use App\Models\Service;
use WMGCore\Repositories\BaseRepository;
use App\Shopify\Models\ShopifyFulfillmentServiceRegistration;

/**
 * Shopify Fulfillment Registration Repository
 */
class ShopifyFSRRepository extends BaseRepository
{
    public function __construct(
        ShopifyFulfillmentServiceRegistration $fulfillmentServiceRegistration
    ) {
        parent::__construct($fulfillmentServiceRegistration);
    }

    public function getAllRegistrations()
    {
        return $this->modelQuery()->cursor();
    }

    public function getRegistrationByService(Service $service)
    {
        return $this->modelQuery()->where('service_id', $service->id)->cursor();
    }

    /**
     * @param $locationId
     * @return \App\Core\Models\Warehouse
     */
    public function getRegistrationByLocationId($locationId)
    {
        return $this->modelQuery()->where('shopify_location_id', $locationId)->first();
    }
}
