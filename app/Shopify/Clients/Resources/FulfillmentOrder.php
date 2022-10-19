<?php
namespace App\Shopify\Clients\Resources;

/**
 * @class AssignedShipmentOrder
 * @package App\Shopify
 *          Shopify API resource
 * @property-read \App\Shopify\Clients\Resources\FulfillmentRequest $FulfillmentRequest
 */
class FulfillmentOrder extends BaseResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'fulfillment_order';
    /**
     * @inheritDoc
     */
    protected $additionalResource= array (
        'FulfillmentRequest' => 'FulfillmentRequest'
    );


    /**
     * @inheritDoc
     */
    protected $customPostActions = array(
        'open',
        'cancel',
        'close'
    );

    protected $childResource = [
        'FulfillmentRequest'
    ];
}
