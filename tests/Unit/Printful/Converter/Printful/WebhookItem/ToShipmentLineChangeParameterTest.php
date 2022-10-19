<?php declare(strict_types=1);

namespace Tests\Unit\Printful\Converter\Printful\WebhookItem;

use App\Models\Order;
use App\Printful\Converter\Printful\WebhookItem\ToShipmentLineChangeParameter;
use App\Printful\Service\PrintfulExternalIdParser;
use Printful\Structures\Webhook\WebhookItem;
use Tests\Feature\Printful\PrintfulApiHelper;
use Tests\Feature\Printful\PrintfulEventGenerator;
use Tests\TestCase;

/**
 * Class ToShipmentLineChangeParameterTest
 * @package Tests\Unit\Printful
 */
class ToShipmentLineChangeParameterTest extends TestCase
{
    /**
     * Order data
     */
    private const ORDER_DATA = [
        "status" => Order::STATUS_DROPPED,
        "sales_channel" => "M113US-Warner Music Store",
        "shipping_method" => "STANDARD",
    ];

    /**
     * Order address data
     */
    private const ORDER_ADDRESS_DATA = [
        "customer_address_type" => "shipping",
    ];

    /**
     * Order items data
     */
    private const PRINTFUL_ORDER_ITEMS_DATA = [
        [
            "source_id" => "PF",
            "currency" => "USD",
            "item_type" => "simple",
            "sku" => "1938234812",
            "custom_attributes" => '{"release_date":"2016-11-25 08:00:00","printful_variant_id":"607094344a22f3"}',
        ],
        [
            "source_id" => "PF",
            "currency" => "USD",
            "item_type" => "simple",
            "sku" => "3828390528",
            "custom_attributes" => '{"release_date":"2016-11-25 08:00:00","printful_variant_id":"60709434263f3"}',
        ]
    ];

    /**
     * @var PrintfulApiHelper
     */
    private $printfulApiHelper;

    /**
     * @var ToShipmentLineChangeParameter
     */
    private $webhookItemConverter;

    /**
     * @var PrintfulEventGenerator
     */
    private $printfulEventGenerator;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->printfulEventGenerator = new PrintfulEventGenerator($this);
        $this->webhookItemConverter = new ToShipmentLineChangeParameter(
            new PrintfulExternalIdParser()
        );
        $this->printfulApiHelper = new PrintfulApiHelper($this);
    }

    /**
     * Test convert method
     *
     * @group Printful
     * @group Converter
     * @dataProvider dataProvider
     * @group inProgress
     * @param string $eventType
     * @param bool   $itemsReturned
     * @param string $reasonCode
     */
    public function testConvert(
        string $eventType,
        bool $itemsReturned,
        string $reasonCode
    ): void {
        $order = $this->printfulApiHelper->createPrintfulOrder(
            self::ORDER_DATA,
            self::ORDER_ADDRESS_DATA,
            self::PRINTFUL_ORDER_ITEMS_DATA
        );

        $webhookItem = WebhookItem::fromArray(
            $this->printfulEventGenerator->packageEventForOrder(
                $order,
                $eventType
            )
        );

        $lineChangeParameter = $this->webhookItemConverter->convert($webhookItem);
        self::assertEquals($order->id, $lineChangeParameter->orderId);
        $lineChangeItems = collect($lineChangeParameter->items);

        foreach ($order->orderItems as $orderItem) {
            $lineChangeItem = $lineChangeItems->firstWhere(
                'orderItemId',
                $orderItem->id
            );

            self::assertNotNull($lineChangeItem);
            self::assertEquals($orderItem->sku, $lineChangeItem->sku);
            self::assertEquals($orderItem->quantity, $lineChangeItem->quantity);
            self::assertEquals($reasonCode, $lineChangeItem->backOrderReasonCode);
            self::assertEquals(0, $lineChangeItem->backorderQuantity);
            self::assertEquals(
                $itemsReturned ? $orderItem->quantity : 0,
                $lineChangeItem->returnedQuantity
            );
        }
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            [
                'order_put_hold',
                false,
                'H',
            ],
            [
                'package_returned',
                true,
                'R',
            ],
        ];
    }
}
