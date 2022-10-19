<?php
namespace App\Shopify\Clients\Resources;

use App\Shopify\Clients\Resources\BaseResource;

/**
 * @class AssignedShipmentOrder
 * @package App\Shopify
 *          Shopify API resource
 * @method accept(array $data)
 * @method reject(array $data)
 * @method fulfillment_request(array $data)
 */
class FulfillmentRequest extends BaseResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'fulfillment_request';

    /**
     * @inheritDoc
     */
    protected $customPostActions = array(
        'accept',
        'reject',
        'fulfillment_request'
    );

    protected function pluralizeKey()
    {
        return $this->resourceKey;
    }
}
