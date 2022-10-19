<?php
namespace App\Shopify\Structures\Shopify;

use App\Models\Service\Model\Serialize;

/**
 * @class Order
 * @package App\Models
 * Shopify Order Object
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Order extends Serialize
{
    public Int $id;
    public ?String $appId;
    public ?String $closedAt;
    public bool $confirmed;
    public String $contactEmail;
    public String $createdAt;
    public String $currency;
    public String $currentSubtotalPrice;

    public String $requestStatus;
    public String $status;
    public Array $supportedActions;
    public ?Address $destination;
    public Array $lineItems;
    public Array $outgoingRequests;
    public String $fulfillmentServiceHandle;
    public ?Address $assignedLocation;
}
