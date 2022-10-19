<?php declare(strict_types=1);

namespace Tests\Unit\Printful\Converter\Local\Order;

use App\Printful\Converter\Local\Order\ToShipmentLineChangeParameter;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class ToShipmentLineChangeParameterTest
 * @package Tests\Unit\Printful
 */
class ToShipmentLineChangeParameterTest extends TestCase
{
    /**
     * OrderItem attributes mapped to line change item parameter attributes
     */
    private const ITEM_TO_ITEM_PARAMETER_MAP = [
        'id' => 'orderItemId',
        'sku' => 'sku',
        'quantity' => 'quantity',
        'quantity_backordered' => 'backorderQuantity',
        'quantity_returned' => 'returnedQuantity',
    ];
    /**
     * Order Data
     */
    private const ORDER_DATA = [
        [
            [
                'status' => 0,
                'sales_channel' => 'M1_US_Warner',
                'request_id' => 1858320,
                'order_id' => '0000888262834766',
                'gift_message' => null,
                'drop_id' => 2389576,
                'shipping_method' => 16,
                'customer_id' => 2983746,
                'customer_reference' => null,
                'vat_country' => null,
                'shipping_net_amount' => 4.10,
                'shipping_gross_amount' => 4.63,
                'shipping_tax_amount' => 0.53,
                'shipping_tax_rate' => 13,
                'shipping_tax_detail' => null,
                'custom_attributes' => <<<JSON
{"store_name": "Warner Music Store", "printful_shipping_method": "STANDARD"}
JSON
            ],
            [
                [
                    'order_line_id' => 957331,
                    'order_line_number' => 745623,
                    'drop_id' => null,
                    'drop_status' => 0,
                    'sku' => '490383467202',
                    'name' => 'Twenty One Pilots T-Shirt, Grey, M',
                    'source_id' => 'PF',
                    'aggregated_line_id' => '',
                    'net_amount' => '14.99',
                    'gross_amount' => '16.04',
                    'tax_amount' => '1.05',
                    'tax_rate' => '7',
                    'currency' => 'USD',
                    'item_type' => 'physical',
                    'parent_order_line_number' => null,
                    'quantity' => 5,
                    'quantity_shipped' => 0,
                    'quantity_ack' => 0,
                    'quantity_backordered' => 0,
                    'quantity_returned' => 0,
                    'custom_attributes' => '{"printful_variant_id":"394524a44a25f9"}',
                ],
                [
                    'order_line_id' => 238572,
                    'order_line_number' => 926452,
                    'drop_id' => null,
                    'drop_status' => 0,
                    'sku' => '393482020572',
                    'name' => 'Twenty One Pilots CD Pack',
                    'source_id' => 'PF',
                    'aggregated_line_id' => '',
                    'net_amount' => '32.99',
                    'gross_amount' => '35.3',
                    'tax_amount' => '2.31',
                    'tax_rate' => '7',
                    'currency' => 'USD',
                    'item_type' => 'physical',
                    'parent_order_line_number' => null,
                    'quantity' => 1,
                    'quantity_shipped' => 0,
                    'quantity_ack' => 0,
                    'quantity_backordered' => 0,
                    'quantity_returned' => 0,
                    'custom_attributes' => '{"printful_variant_id":"937492344a22f3"}',
                ]
            ],
            null
        ],
        [
            [
                'status' => 0,
                'sales_channel' => 'M113_EU_store',
                'request_id' => 30472,
                'order_id' => '0000888220363482',
                'gift_message' => null,
                'drop_id' => 2389576,
                'shipping_method' => 16,
                'customer_id' => 194920,
                'customer_reference' => null,
                'vat_country' => null,
                'shipping_net_amount' => 5.82,
                'shipping_gross_amount' => 8.48,
                'shipping_tax_amount' => 2.66,
                'shipping_tax_rate' => 46,
                'shipping_tax_detail' => null,
                'custom_attributes' => <<<JSON
{"store_name": "Warner Music Store", "tax_id": "CPF/CNPJ:123452678-90","printful_shipping_method": "EXPRESS"}
JSON
            ],
            [
                [
                    'order_line_id' => 8378292,
                    'order_line_number' => 282735,
                    'drop_id' => null,
                    'drop_status' => 0,
                    'sku' => '39234750238840',
                    'name' => 'Twenty One Pilots Sweater, Gray, XL',
                    'source_id' => 'PF',
                    'aggregated_line_id' => '',
                    'net_amount' => '24.9900',
                    'gross_amount' => '26.7400',
                    'tax_amount' => '1.75',
                    'tax_rate' => '7',
                    'currency' => 'EUR',
                    'item_type' => 'physical',
                    'parent_order_line_number' => null,
                    'quantity' => 3,
                    'quantity_shipped' => 3,
                    'quantity_ack' => 30,
                    'quantity_backordered' => 0,
                    'quantity_returned' => 2,
                    'custom_attributes' => '{"printful_variant_id":"2937528a9378b3"}',
                ],
                [
                    'order_line_id' => 8378292,
                    'order_line_number' => 282735,
                    'drop_id' => null,
                    'drop_status' => 0,
                    'sku' => '39234750238840',
                    'name' => 'Twenty One Pilots Phone Case',
                    'source_id' => 'PF',
                    'aggregated_line_id' => '',
                    'net_amount' => '8.9900',
                    'gross_amount' => '9.6200',
                    'tax_amount' => '0.6300',
                    'tax_rate' => '7.0000',
                    'currency' => 'EUR',
                    'item_type' => 'physical',
                    'parent_order_line_number' => null,
                    'quantity' => 1,
                    'quantity_shipped' => 1,
                    'quantity_ack' => 1,
                    'quantity_backordered' => 0,
                    'quantity_returned' => 1,
                    'custom_attributes' => '{"printful_variant_id":"287382a9973e9f"}',
                ]
            ],
            "R",
        ]
    ];

    /**
     * @var ToShipmentLineChangeParameter
     */
    private $orderConverter;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->orderConverter = new ToShipmentLineChangeParameter();
    }

    /**
     * Test convert method
     *
     * @group Printful
     * @dataProvider orderDataProvider
     * @group Converter
     * @param array       $orderData
     * @param Collection  $itemsData
     * @param string|null $backorderReasonCode
     */
    public function testConvert(
        array $orderData,
        Collection $itemsData,
        ?string $backorderReasonCode
    ): void {
        $this->helper->ordersWithItems();
        $order = $this->helper->order($orderData);

        $orderItems = $itemsData->map(function (array $data) use ($order) {
            $data['parent_id'] = $order->id;
            return $this->helper->orderItem($data);
        });

        $lineChangeParameter = $this->orderConverter->convert(
            $order,
            $orderItems,
            $backorderReasonCode
        );

        self::assertEquals($order->id, $lineChangeParameter->orderId);
        self::assertCount($itemsData->count(), $lineChangeParameter->items);
        self::assertEqualsCanonicalizing(
            $lineChangeParameter->getOrderItemIds(),
            $orderItems->pluck('id')->toArray()
        );

        $resultItems = collect($lineChangeParameter->items);

        $orderItems->each(
            function ($orderItem) use ($resultItems, $backorderReasonCode) {
                $item = $resultItems->firstWhere('orderItemId', $orderItem->id);
                self::assertNotNull($item);

                $map = self::ITEM_TO_ITEM_PARAMETER_MAP;
                foreach ($map as $orderItemProperty => $lineItemProperty) {
                    self::assertEquals(
                        $orderItem->$orderItemProperty,
                        $item->$lineItemProperty
                    );
                }

                self::assertEquals(
                    $backorderReasonCode,
                    $item->backOrderReasonCode
                );
            }
        );
    }

    /**
     * @return array
     */
    public function orderDataProvider(): array
    {
        $orderData = self::ORDER_DATA;

        foreach ($orderData as &$case) {
            $case[1] = collect($case[1]);
        }

        return $orderData;
    }
}
