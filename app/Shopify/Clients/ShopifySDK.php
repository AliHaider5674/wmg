<?php
namespace App\Shopify\Clients;

use App\Shopify\Clients\Traits\ResourceLoader;

/**
 * Extends Shopify SDK
 * @property-read \App\Shopify\Clients\Resources\AssignedShipmentOrder AssignedShipmentOrder
 * @property-read \App\Shopify\Clients\Resources\FulfillmentOrder      FulfillmentOrder
 * @property-read \App\Shopify\Clients\Resources\Fulfillment           Fulfillment
 * @method \App\Shopify\Clients\Resources\FulfillmentOrder FulfillmentOrder(int $id)
 */
class ShopifySDK extends \PHPShopify\ShopifySDK
{
    use ResourceLoader;
    protected array $additionalResource = [
        'AssignedShipmentOrder',
        'FulfillmentOrder',
        'Fulfillment',
        'FulfillmentRequest'

    ];
    private const DEFAULT_API_VERSION = '2021-07';
    public function __construct($config = array())
    {
        self::$defaultApiVersion = static::DEFAULT_API_VERSION;
        parent::__construct($config);
    }
}
