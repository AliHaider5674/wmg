<?php
namespace App\Shopify\Factories\Shopify;

use App\Shopify\Structures\Shopify\FulfillmentOrder;
use Illuminate\Contracts\Foundation\Application;

/**
 * @class FulfillmentOrderFactory
 * @package App\Shopify
 */
class FulfillmentOrderFactory
{
    private Application $app;
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    public function createFromUnderScore(Array $data) : FulfillmentOrder
    {
        /** @var FulfillmentOrder $shipment */
        $shipment = $this->app->make(FulfillmentOrder::class);
        $shipment->fill($data, false);
        return $shipment;
    }
}
