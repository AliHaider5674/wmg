<?php
namespace App\Shopify\Clients\Resources;

/**
 * @class AssignedShipmentOrder
 * @package App\Shopify
 *          Shopify API resource
 */
class AssignedShipmentOrder extends BaseResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'assigned_fulfillment_order';
}
