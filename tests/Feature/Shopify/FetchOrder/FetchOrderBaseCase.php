<?php

namespace Tests\Feature\Shopify\FetchOrder;

use App\Core\Models\Warehouse;
use App\Models\Service;
use App\Shopify\Clients\ShopifySDK;
use App\Shopify\Services\ClientService;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * Class FetchOrderBaseCase
 * @package Tests\Feature\Shopify\FetchOrder
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
abstract class FetchOrderBaseCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setTestService();
        $this->fulfillmentId = 5340547514521;
        $this->mockProcessor = $this->getMockProcessor();
        $this->shipmentOrder = $this->getTestShipmentOrder();
        $this->service = $this->getService();
        $this->client = $this->getClient($this->service);
    }

    /**
     * setTestService
     */
    protected function setTestService()
    {
        $user = User::factory()->create();
        //Register services
        $service = [
            "app_id" => "shopify",
            "app_url" => "https://wmg-sandbox.myshopify.com",
            "name" => "shopify",
            "client" => "shopify.restful",
            "events" => ["*"],
            "event_rules" => [],
            "addition" => [
                "shop_url" => "http://shopify.test",
                "api_key" => "developer",
                "password" => "password1"
            ]
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);
        $response->assertStatus(200);
    }

    /**
     * getMockProcessor
     * @return mixed
     */
    abstract protected function getMockProcessor();

    /**
     * getService
     * @return Builder|Model|object|null
     */
    protected function getService()
    {
        return Service::query()->where('app_id', '=', 'shopify')->first();
    }

    protected function getClient($service): ShopifySDK
    {
        $clientService = $this->app->make(ClientService::class);

        /**
         * @var ClientService $clientService
         */
        return $clientService->getClient($service);
    }

    /**
     * getTestShipmentOrder
     * @return array
     */
    protected function getTestShipmentOrder(): array
    {
        return array (
            'id' => $this->fulfillmentId,
            'shop_id' => 58699514009,
            'order_id' => 4346732642457,
            'assigned_location_id' => 65279754393,
            'request_status' => 'submitted',
            'status' => 'open',
            'supported_actions' =>
                array (
                    0 => 'cancel_fulfillment_order',
                ),
            'destination' =>
                array (
                    'id' => 5185362657433,
                    'address1' => 'Broadway',
                    'address2' => '',
                    'city' => 'New York',
                    'company' => 'London',
                    'country' => 'United States',
                    'email' => 'test.user@warnermusic.com',
                    'first_name' => 'test',
                    'last_name' => 'user',
                    'phone' => '',
                    'province' => 'New York',
                    'zip' => '90012',
                ),
            'line_items' =>
                array (
                    0 =>
                        array (
                            'id' => 11262058594457,
                            'shop_id' => 58699514009,
                            'fulfillment_order_id' => $this->fulfillmentId,
                            'quantity' => 1,
                            'line_item_id' => 11127543693465,
                            'inventory_item_id' => 43468108595353,
                            'fulfillable_quantity' => 1,
                            'variant_id' => 41371884519577,
                        ),
                ),
            'outgoing_requests' =>
                array (
                    0 =>
                        array (
                            'message' => '',
                            'request_options' =>
                                array (
                                    'notify_customer' => false,
                                ),
                            'sent_at' => '2022-02-21T05:53:05-08:00',
                            'kind' => 'fulfillment_request',
                        ),
                ),
            'fulfill_at' => null,
            'international_duties' => null,
            'delivery_method' => null,
            'assigned_location' =>
                array (
                    'address1' => null,
                    'address2' => null,
                    'city' => null,
                    'country_code' => 'US',
                    'location_id' => 65279754393,
                    'name' => 'Gnarlywood',
                    'phone' => null,
                    'province' => null,
                    'zip' => null,
                ),
        );
    }

    /**
     * fetchOrderReturnValue
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function fetchOrderReturnValue(): array
    {
        return array (
            'id' => 4346732642457,
            'admin_graphql_api_id' => 'gid://shopify/Order/4346732642457',
            'app_id' => 1354745,
            'browser_ip' => null,
            'buyer_accepts_marketing' => false,
            'cancel_reason' => null,
            'cancelled_at' => null,
            'cart_token' => null,
            'checkout_id' => 23874912649369,
            'checkout_token' => '86ee2af4b9153d84564188701dcd4b4e',
            'client_details' =>
                array (
                    'accept_language' => null,
                    'browser_height' => null,
                    'browser_ip' => null,
                    'browser_width' => null,
                    'session_hash' => null,
                    'user_agent' => null,
                ),
            'closed_at' => null,
            'confirmed' => true,
            'contact_email' => 'test.user@warnermusic.com',
            'created_at' => '2022-02-21T05:51:50-08:00',
            'currency' => 'USD',
            'current_subtotal_price' => '10.00',
            'current_subtotal_price_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'current_total_discounts' => '0.00',
            'current_total_discounts_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'current_total_duties_set' => null,
            'current_total_price' => '10.00',
            'current_total_price_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'current_total_tax' => '0.00',
            'current_total_tax_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'customer_locale' => 'en',
            'device_id' => null,
            'discount_codes' =>
                array (
                ),
            'email' => 'test.user@warnermusic.com',
            'estimated_taxes' => false,
            'financial_status' => 'paid',
            'fulfillment_status' => null,
            'gateway' => 'manual',
            'landing_site' => null,
            'landing_site_ref' => null,
            'location_id' => null,
            'name' => '#1470',
            'note' => null,
            'note_attributes' =>
                array (
                    0 =>
                        array (
                            'name' => 'Kount Risk Assessment',
                            'value' => 'Kount has performed a Risk Analysis, result: [Auto Approve]',
                        ),
                ),
            'number' => 470,
            'order_number' => 1470,
            'order_status_url' => 'https://wmg-sandbox.myshopify.com/58699514009/orders/bdf3f6d46',
            'original_total_duties_set' => null,
            'payment_gateway_names' =>
                array (
                    0 => 'manual',
                ),
            'phone' => null,
            'presentment_currency' => 'USD',
            'processed_at' => '2022-02-21T05:51:50-08:00',
            'processing_method' => 'manual',
            'reference' => null,
            'referring_site' => null,
            'source_identifier' => null,
            'source_name' => '1830279',
            'source_url' => null,
            'subtotal_price' => '10.00',
            'subtotal_price_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'tags' => '',
            'tax_lines' =>
                array (
                ),
            'taxes_included' => false,
            'test' => false,
            'token' => 'bdf3f6d4670c4e1f1d84fde8ba82cba6',
            'total_discounts' => '0.00',
            'total_discounts_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'total_line_items_price' => '10.00',
            'total_line_items_price_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'total_outstanding' => '0.00',
            'total_price' => '10.00',
            'total_price_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '10.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'total_price_usd' => '10.00',
            'total_shipping_price_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'total_tax' => '0.00',
            'total_tax_set' =>
                array (
                    'shop_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                    'presentment_money' =>
                        array (
                            'amount' => '0.00',
                            'currency_code' => 'USD',
                        ),
                ),
            'total_tip_received' => '0.00',
            'total_weight' => 0,
            'updated_at' => '2022-02-21T05:51:57-08:00',
            'user_id' => 76564201625,
            'billing_address' =>
                array (
                    'first_name' => 'test',
                    'address1' => 'Broadway',
                    'phone' => '',
                    'city' => 'New York',
                    'zip' => '90012',
                    'province' => 'New York',
                    'country' => 'United States',
                    'last_name' => 'User',
                    'address2' => '',
                    'company' => 'London',
                    'latitude' => 34.064081,
                    'longitude' => -118.2374592,
                    'name' => 'Test User',
                    'country_code' => 'US',
                    'province_code' => 'NY',
                ),
            'customer' =>
                array (
                    'id' => 5617370005657,
                    'email' => 'test.user@warnermusic.com',
                    'accepts_marketing' => false,
                    'created_at' => '2021-09-22T03:51:39-07:00',
                    'updated_at' => '2022-02-21T05:51:51-08:00',
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'orders_count' => 74,
                    'state' => 'disabled',
                    'total_spent' => '945.90',
                    'last_order_id' => 4346732642457,
                    'note' => null,
                    'verified_email' => true,
                    'multipass_identifier' => null,
                    'tax_exempt' => false,
                    'phone' => null,
                    'tags' => '',
                    'last_order_name' => '#1470',
                    'currency' => 'USD',
                    'accepts_marketing_updated_at' => '2021-09-22T03:51:39-07:00',
                    'marketing_opt_in_level' => null,
                    'tax_exemptions' =>
                        array (
                        ),
                    'admin_graphql_api_id' => 'gid://shopify/Customer/5617370005657',
                    'default_address' =>
                        array (
                            'id' => 6847656427673,
                            'customer_id' => 5617370005657,
                            'first_name' => 'Test',
                            'last_name' => 'User',
                            'company' => 'London',
                            'address1' => 'Broadway',
                            'address2' => '',
                            'city' => 'New York',
                            'province' => 'New York',
                            'country' => 'United States',
                            'zip' => '90012',
                            'phone' => '',
                            'name' => 'Test User',
                            'province_code' => 'NY',
                            'country_code' => 'US',
                            'country_name' => 'United States',
                            'default' => true,
                        ),
                ),
            'discount_applications' =>
                array (
                ),
            'fulfillments' =>
                array (
                ),
            'line_items' =>
                array (
                    0 =>
                        array (
                            'id' => 11127543693465,
                            'admin_graphql_api_id' => 'gid://shopify/LineItem/11127543693465',
                            'destination_location' =>
                                array (
                                    'id' => 3159808508057,
                                    'country_code' => 'US',
                                    'province_code' => 'NY',
                                    'name' => 'Test User',
                                    'address1' => 'Broadway',
                                    'address2' => '',
                                    'city' => 'New York',
                                    'zip' => '90012',
                                ),
                            'fulfillable_quantity' => 0,
                            'fulfillment_service' => 'gnarlywood',
                            'fulfillment_status' => null,
                            'gift_card' => false,
                            'grams' => 0,
                            'name' => 'Product A',
                            'origin_location' =>
                                array (
                                    'id' => 3135590596761,
                                    'country_code' => 'US',
                                    'province_code' => 'SC',
                                    'name' => 'WMG Sandbox',
                                    'address1' => '145 Williman St.',
                                    'address2' => '',
                                    'city' => 'Charleston',
                                    'zip' => '29403',
                                ),
                            'pre_tax_price' => '10.00',
                            'pre_tax_price_set' =>
                                array (
                                    'shop_money' =>
                                        array (
                                            'amount' => '10.00',
                                            'currency_code' => 'USD',
                                        ),
                                    'presentment_money' =>
                                        array (
                                            'amount' => '10.00',
                                            'currency_code' => 'USD',
                                        ),
                                ),
                            'price' => '10.00',
                            'price_set' =>
                                array (
                                    'shop_money' =>
                                        array (
                                            'amount' => '10.00',
                                            'currency_code' => 'USD',
                                        ),
                                    'presentment_money' =>
                                        array (
                                            'amount' => '10.00',
                                            'currency_code' => 'USD',
                                        ),
                                ),
                            'product_exists' => true,
                            'product_id' => 7154906103961,
                            'properties' =>
                                array (
                                ),
                            'quantity' => 1,
                            'requires_shipping' => true,
                            'sku' => '0090317687704',
                            'taxable' => true,
                            'title' => 'Product A',
                            'total_discount' => '0.00',
                            'total_discount_set' =>
                                array (
                                    'shop_money' =>
                                        array (
                                            'amount' => '0.00',
                                            'currency_code' => 'USD',
                                        ),
                                    'presentment_money' =>
                                        array (
                                            'amount' => '0.00',
                                            'currency_code' => 'USD',
                                        ),
                                ),
                            'variant_id' => 41371884519577,
                            'variant_inventory_management' => 'gnarlywood',
                            'variant_title' => '',
                            'vendor' => 'WMG Sandbox',
                            'tax_lines' =>
                                array (
                                ),
                            'duties' =>
                                array (
                                ),
                            'discount_allocations' =>
                                array (
                                ),
                        ),
                ),
            'refunds' =>
                array (
                ),
            'shipping_address' =>
                array (
                    'first_name' => 'Test',
                    'address1' => 'Broadway',
                    'phone' => '',
                    'city' => 'New York',
                    'zip' => '90012',
                    'province' => 'New York',
                    'country' => 'United States',
                    'last_name' => 'User',
                    'address2' => '',
                    'company' => 'London',
                    'latitude' => 34.064081,
                    'longitude' => -118.2374592,
                    'name' => 'Test User',
                    'country_code' => 'US',
                    'province_code' => 'NY',
                ),
            'shipping_lines' =>
                array (
                ),
        );
    }

    /**
     * getWarehouseReturnValue
     * @return mixed
     */
    protected function getWarehouseReturnValue()
    {
        return Warehouse::create(
            [
                'id' => 1,
                'code' => 'GNAR',
                'name' => 'Gnarlywood',
                'region' => 1,
                'status' => 1
            ]
        );
    }
}
