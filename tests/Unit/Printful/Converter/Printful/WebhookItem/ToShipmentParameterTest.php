<?php declare(strict_types=1);

namespace Tests\Unit\Printful\Converter\Printful\WebhookItem;

use App\Models\Order;
use App\Printful\Configurations\PrintfulConfig;
use App\Printful\Converter\Printful\WebhookItem\ToShipmentParameter;
use App\Printful\Service\PrintfulExternalIdParser;
use Printful\Structures\Webhook\WebhookItem;
use Tests\Feature\Printful\PrintfulApiHelper;
use Tests\Feature\Printful\PrintfulEventGenerator;
use Tests\TestCase;

/**
 * Class ToShipmentParameterTest
 * @package Tests\Unit\Printful
 */
class ToShipmentParameterTest extends TestCase
{
    /**
     * Carrier Map Configuration
     */
    private const CARRIER_MAP = [
        ["exp" => "^UPS", "carrier" => "ups"],
        ["exp" => "^FEDEX", "carrier" => "fedex"],
        ["exp" => "^USPS", "carrier" => "usps"],
        ["exp" => ".*", "carrier" => "Default Carrier Value"],
    ];

    /**
     * Test Carrier Data for Data provider
     */
    private const TEST_CARRIER_DATA = [
        'UPS_Something' => self::CARRIER_MAP[0]['carrier'],
        'FEDEX' => self::CARRIER_MAP[1]['carrier'],
        'USPS Carrier' => self::CARRIER_MAP[2]['carrier'],
        'Non Matching Carrier' => self::CARRIER_MAP[3]['carrier'],
    ];

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
     * @var ToShipmentParameter
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
        $printfulConfig = \Mockery::mock(PrintfulConfig::class);
        $printfulConfig->shouldReceive('getCarrierExpMap')->once()->andReturn(
            self::CARRIER_MAP
        );
        $printfulExternalIdParser = new PrintfulExternalIdParser();
        $this->webhookItemConverter = new ToShipmentParameter(
            $printfulConfig,
            $printfulExternalIdParser
        );
        $this->printfulApiHelper = new PrintfulApiHelper($this);
    }

    /**
     * Test convert method
     *
     * @group        Printful
     * @group        Converter
     * @dataProvider dataProvider
     * @group        inProgress
     * @param string $eventCarrier
     * @param        $outputCarrier
     */
    public function testConvert(
        string $eventCarrier,
        string $outputCarrier
    ): void {
        $order = $this->printfulApiHelper->createPrintfulOrder(
            self::ORDER_DATA,
            self::ORDER_ADDRESS_DATA,
            self::PRINTFUL_ORDER_ITEMS_DATA
        );
        $trackingNumber = '12345';
        $service = 'UPS';
        $location = 'US';
        $webhookItem = WebhookItem::fromArray(
            $this->printfulEventGenerator->packageEventForOrder(
                $order,
                'package_shipped',
                $trackingNumber,
                $eventCarrier,
                $service,
                $location
            )
        );

        $shipmentParameter = $this->webhookItemConverter->convert(
            $webhookItem
        );
        self::assertEquals($order->id, $shipmentParameter->orderId);
        $package = current($shipmentParameter->packages);
        self::assertEquals($outputCarrier, $package->carrier);
        self::assertEquals($trackingNumber, $package->trackingNumber);
        self::assertEquals("https://www.printful.com/", $package->trackingLink);
        $packageItems = collect($package->getHiddenItems());
        $orderItems = $order->orderItems;
        self::assertCount($orderItems->count(), $package->itemIds);
        self::assertCount($orderItems->count(), $package->shippedQtyMap);

        foreach ($orderItems as $orderItem) {
            $packageItem = $packageItems->firstWhere('orderItemId', $orderItem->id);
            self::assertNotNull($packageItem);
            self::assertEquals($orderItem->sku, $packageItem->sku);
            self::assertEquals($orderItem->quantity, $packageItem->quantity);
            self::assertEquals($orderItem->backorderQuantity, 0);
            self::assertEquals($orderItem->returnedQuantity, 0);
            self::assertEquals($orderItem->backorderReasonCode, null);
            self::assertContains($orderItem->id, $package->itemIds);
            self::assertEquals(
                $package->shippedQtyMap[$orderItem->id],
                $orderItem->quantity
            );
        }
    }

    /**
     * @return \Generator
     */
    public function dataProvider(): \Generator
    {
        foreach (self::TEST_CARRIER_DATA as $key => $value) {
            yield [
                $key,
                $value
            ];
        }
    }
}
