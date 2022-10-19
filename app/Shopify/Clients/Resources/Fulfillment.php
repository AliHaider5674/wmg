<?php
namespace App\Shopify\Clients\Resources;

use App\Shopify\Clients\Resources\BaseResource;

/**
 * @class AssignedShipmentOrder
 * @package App\Shopify
 *          Shopify API resource
 * @method accept(array $data)
 * @method reject(array $data)
 */
class Fulfillment extends BaseResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'fulfillment';
}
