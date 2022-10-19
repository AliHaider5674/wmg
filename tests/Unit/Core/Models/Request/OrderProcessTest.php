<?php
namespace Tests\Unit\Core\Models\Request;

use App\Exceptions\OrderReceiveException;
use App\Models\OrderItem;
use App\Models\Request\OrderProcessor;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test File System
 *
 * Class FileSystemTest
 * @category WMG
 * @package  Tests\Unit\Mdc\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderProcessTest extends TestCase
{
    /**
     * @var OrderProcessor
     */
    private OrderProcessor $orderProcessor;

    /**
     * @var array
     */
    private array $sampleBody;

    /**
     * @var array|array[]
     */
    private array $sampleItems;

    /**
     * Set up tests
     * @SuppressWarnings(PHPMD)
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->orderProcessor = new OrderProcessor($this->app->make(Validator::class));
        $this->sampleBody = [
            'request_id' => 'STG-Bruno-Mars-001-training-01',
            'sales_channel' => 'STG-Bruno-Mars',
            'order_id' => '001-training',
            'source_id' => 'US',
            'shipping_method' => 'STANDARD',
            'shipping_price' => [
                    'net_amount' => 10,
                    'gross_amount' => 10,
                    'tax_amount' => 0,
                    'tax_rate' => 0,
                    'taxes' => [[
                                'type' => 'NO TAX',
                                'amount' => 0,
                                'rate' => 0,
                            ],
                        ],
                    'currency' => 'USD',
                ],
            'shipping_address' => [
                    'address_type' => 'customer',
                    'first_name' => 'Daria',
                    'last_name' => 'Bernabei',
                    'address1' => '1633 Broadway',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10019',
                    'country_code' => 'US',
                    'phone' => '111111111',
                    'email' => 'test@wmg.com',
                    'custom_attributes' => []
                ],
            'billing_address' => [
                    'address_type' => 'customer',
                    'first_name' => 'Daria',
                    'last_name' => 'Bernabei',
                    'address1' => '1633 Broadway',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10019',
                    'country_code' => 'US',
                    'phone' => '111111',
                    'email' => 'test@wmg.com',
                    'custom_attributes' => []
                ],
            'custom_details' => [
                        [
                            'name' => 'shipping_description',
                            'value' => 'Flat Rate - Fixed',
                        ],
                ],
            'items' => [],
            'aggregated_items' => [
                        [
                            'aggregated_line_id' => '1',
                            'sku' => 'mariadjtest',
                            'quantity' => 2,
                            'order_lines' => [
                                    1,
                                    2,
                                ],
                        ],
                ],
            'created' => '2019-04-17T08:00:24+00:00',
            'customer_id' => '2',
            'language' => 'en_US',
            'vat_country' => 'US',
            'request_created_at' => '2019-04-19T21:46:05+00:00',
        ];

        $this->sampleItems = [
            [
                'order_line_id' => '302',
                'order_line_number' => 1,
                'item_type' => 'PHYSICAL',
                'sku' => 'mariadjtest',
                'name' => 'Maria DJ CD Test',
                'custom_details' => [],
                'image_url' => null,
                'order_line_price' => [
                    'net_amount' => 9.99,
                    'gross_amount' => 9.99,
                    'tax_amount' => 0,
                    'tax_rate' => 0,
                    'taxes' => [
                        [
                            'type' => 'NO TAX',
                            'amount' => 0,
                            'rate' => 0,
                        ],
                    ],
                    'currency' => 'USD',
                ],
                'order_line_promotions_info' => [
                    'original_price' => 9.99,
                    'promotions' => [],
                ],
                'status' => 'NEW',
                'status_reason' => 'ITEM_PENDING_PICKING',
            ],
            [
                'order_line_id' => '302',
                'order_line_number' => 2,
                'item_type' => 'PHYSICAL',
                'sku' => 'mariadjtest',
                'name' => 'Maria DJ CD Test',
                'custom_details' => [],
                'image_url' => null,
                'order_line_price' => [
                    'net_amount' => 9.99,
                    'gross_amount' => 9.99,
                    'tax_amount' => 0,
                    'tax_rate' => 0,
                    'taxes' =>[
                        [
                            'type' => 'NO TAX',
                            'amount' => 0,
                            'rate' => 0,
                        ],
                    ],
                    'currency' => 'USD',
                ],
                'order_line_promotions_info' => [
                    'original_price' => 9.99,
                    'promotions' => [],
                ],
                'status' => 'NEW',
                'status_reason' => 'ITEM_PENDING_PICKING',
            ]];
        $this->sampleBody['items'] = $this->sampleItems;
    }


    public function testReceiveTwoSameItemsWithQuantityOneSuccess()
    {
        $this->orderProcessor->save($this->sampleBody);
        $orderItems = OrderItem::get();
        foreach ($orderItems as $orderItem) {
            $this->assertEquals(1, $orderItem->quantity);
        }
    }

    public function testReceiveTwoItemsWithMultipleQuantityError()
    {
        $this->sampleBody['aggregated_items'] = [[
            'aggregated_line_id' => '1',
            'sku' => 'mariadjtest',
            'quantity' => 3,
            'order_lines' => [
                1,
                2,
            ],
        ]];
        $this->expectException(OrderReceiveException::class);
        $this->orderProcessor->save($this->sampleBody);
    }


    public function testStandardOrderReceive()
    {
        $items = $this->sampleItems;
        $items[0]['sku'] = 'abc';
        $items[1]['sku'] = 'efg';
        $this->sampleBody['aggregated_items'] = [
            [
                'aggregated_line_id' => '1',
                'sku' => 'abc',
                'quantity' => 3,
                'order_lines' => [
                    1
                ],
            ],
            [
                'aggregated_line_id' => '2',
                'sku' => 'efg',
                'quantity' => 1,
                'order_lines' => [
                    2
                ],
            ]
        ];
        $this->sampleBody['items'] = $items;
        $this->orderProcessor->save($this->sampleBody);
        $orderItems = OrderItem::get();
        foreach ($orderItems as $orderItem) {
            if ($orderItem->sku === 'abc') {
                $this->assertEquals(3, (int) $orderItem->quantity);
                continue;
            }
            $this->assertEquals(1, (int) $orderItem->quantity);
        }
    }
}
