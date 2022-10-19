<?php declare(strict_types=1);

namespace Tests\Feature\Printful;

use App\Mdc\Service\Event\ClientHandler\ShipmentHandler;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery as M;
use Printful\PrintfulApiClient;
use App\Mdc\Clients\SoapClient;
use Tests\Helper;
use Tests\TestCase;

/**
 * Class OrderShippedTest
 * @package Tests\Feature\Printful
 */
class OrderShippedTest extends TestCase
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
            "custom_attributes" => '{"release_date":"2016-11-25 08:00:00","printful_variant_id":"607094344a22f3"}',
        ],
        [
            "source_id" => "PF",
            "currency" => "USD",
            "item_type" => "simple",
            "custom_attributes" => '{"release_date":"2016-11-25 08:00:00","printful_variant_id":"60709434263f3"}',
        ]
    ];

    /**
     * @var PrintfulApiClient
     */
    private $printfulApi;

    /**
     * @var PrintfulApiHelper
     */
    private $printfulApiHelper;

    /**
     * @var M\LegacyMockInterface|M\MockInterface|SoapClient
     */
    private $m1SoapClient;

    /**
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->printfulApi = M::mock(PrintfulApiClient::class);
        $this->app->instance(PrintfulApiClient::class, $this->printfulApi);
        $this->helper->setUpMdcServiceEvents(['item.warehouse.received']);
        $this->m1SoapClient = $this->getHelper()
            ->mockM1SoapClient([ShipmentHandler::class]);
        $this->printfulApiHelper = new PrintfulApiHelper($this);
    }

    /**
     * Test that handling an on hold event will trigger a SOAP request to Magento
     *
     * @group printful
     * @group integration
     * @group shipped
     */
    public function testOrderOnShippedEventHandleSendsShippedSoapRequestToMagento(): void
    {
        $this->printfulApiHelper->clearPrintfulOrders();
        $this->printfulApiHelper->clearPrintfulEvents();
        $printfulEventGenerator = new PrintfulEventGenerator($this);

        $order = $this->printfulApiHelper->createPrintfulOrder(
            self::ORDER_DATA,
            self::ORDER_ADDRESS_DATA,
            self::PRINTFUL_ORDER_ITEMS_DATA
        );

        $event = $printfulEventGenerator->createShipmentEvent($order);

        self::assertEquals(
            PrintfulEventGenerator::EVENT_STATUS_RECEIVED,
            $event->status
        );

        $shippedItems = $order->orderItems()->sourceId('PF')->get()
            ->map(function (OrderItem $orderItem) {
                return [
                    'order_item_id' => $orderItem->order_line_id,
                    'qty' => $orderItem->quantity
                ];
            })->toArray();

        $this->helper->setUpMdcServiceEvents(['item.shipped']);

        $this->m1SoapClient->expects('salesOrderShipmentCreate')->withArgs([
            Helper::M1_SOAP_TOKEN,
            $order->order_id,
            $shippedItems,
            null,
            false
        ]);

        $this->artisan('wmg:fulfillment pf.shipment');

        self::assertEquals(
            PrintfulEventGenerator::EVENT_STATUS_PROCESSED,
            $event->refresh()->status
        );
    }
}
