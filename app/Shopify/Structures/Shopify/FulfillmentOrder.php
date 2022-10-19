<?php
namespace App\Shopify\Structures\Shopify;

use App\Models\Service\Model\Serialize;

/**
 * @class FulfillmentOrder
 * @package App\Models
 * Fulfillment order object
 */
class FulfillmentOrder extends Serialize
{
    public Int $id;
    public Int $shopId;
    public Int $orderId;
    public Int $assignedLocationId;
    public String $requestStatus;
    public String $status;
    public Array $supportedActions;
    public ?Address $destination;
    public Array $lineItems;
    public Array $outgoingRequests;
    public String $fulfillmentServiceHandle;
    public ?Address $assignedLocation;
}
