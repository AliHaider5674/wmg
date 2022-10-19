<?php declare(strict_types=1);

namespace Tests\Feature\Printful;

use App\Core\Enums\OrderItemStatus;
use App\Mdc\Service\Event\ClientHandler\AckHandler;
use App\Models\Order;
use App\Models\OrderItem;
use App\Printful\Service\PrintfulCountryService;
use Exception;
use Printful\PrintfulApiClient;
use Mockery as M;
use App\Mdc\Clients\SoapClient;
use Tests\Helper;
use Tests\TestCase;
use App\Models\Service\Model\ShipmentLineChange\Item;
use Printful\Exceptions\PrintfulApiException;

/**
 * Class CreateOrderTest
 * @package Tests\Feature\Printful
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateOrderTest extends TestCase
{
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
     * Order data
     */
    private const ORDER_DATA = [
        "status" => Order::STATUS_RECEIVED,
        "sales_channel" => "M113US-Warner Music Store",
        "custom_attributes" => '{"shipping_method": "STANDARD"}',
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
     * Non printful order items data
     */
    private const NON_PRINTFUL_ORDER_ITEMS_DATA = [
        [
            "source_id" => "US",
            "currency" => "USD",
            "item_type" => "simple",
        ],
        [
            "source_id" => "US",
            "currency" => "USD",
            "item_type" => "simple",
        ]
    ];

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->printfulApi = M::mock(PrintfulApiClient::class);
        $this->app->instance(PrintfulApiClient::class, $this->printfulApi);
        $this->helper->setUpMdcServiceEvents(['item.warehouse.received']);
        $this->m1SoapClient = $this->getHelper()
            ->mockM1SoapClient([AckHandler::class]);
        $this->printfulApiHelper = new PrintfulApiHelper($this);
        $this->printfulApi->allows()
            ->get(PrintfulCountryService::URI)
            ->andReturns($this->printfulApiHelper->getPrintfulCountries());
    }

    /**
     * Test that an order drop will send it to Printful
     * @group printful
     * @group integration
     * @group order
     * @group ordercreated
     * @throws Exception
     */
    public function testCreateOrderSuccessfullySendsToPrintful(): void
    {
        $this->orderShouldBeSentToPrintful();
        $this->artisan('wmg:fulfillment pf.order');
    }

    /**
     * Test that dropping a printful order will mark only the printful items as dropped
     * @group printful
     * @group integration
     * @group order
     * @group ordercreated
     */
    public function testCreateOrderWillMarkOnlyPrintfulOrderItemsAsDropped(): void
    {
        $order = $this->orderShouldBeSentToPrintful();

        foreach (self::NON_PRINTFUL_ORDER_ITEMS_DATA as $orderItemAttributes) {
            $this->helper->createOrderItem(
                array_merge($orderItemAttributes, [
                    'parent_id' => $order->id,
                ])
            );
        }

        self::assertSame(4, $order->orderItems()->count());

        $this->artisan('wmg:fulfillment pf.order');

        $pfItems = $order->orderItems()->sourceId('PF');
        $nonPfItems = $order->orderItems()->where('source_id', '!=', 'PF');

        $pfItemCount = 0;
        $nonPfItemCount = 0;

        $pfItems->each(
            static function (OrderItem $item) use (&$pfItemCount) {
                $pfItemCount++;
                self::assertEquals(
                    OrderItemStatus::DROPPED,
                    $item->drop_status
                );
            }
        );

        $nonPfItems->each(
            static function (OrderItem $item) use (&$nonPfItemCount) {
                $nonPfItemCount++;
                self::assertEquals(
                    OrderItemStatus::RECEIVED,
                    $item->drop_status
                );
            }
        );

        self::assertSame(2, $pfItemCount);
        self::assertSame(2, $nonPfItemCount);
    }

    /**
     * Test that dropping a printful order will mark only the printful items as dropped
     * @group printful
     * @group integration
     * @group order
     * @group ordercreated
     */
    public function testCreateOrderWillSetOnlyPrintfulOrderItemsDropId(): void
    {
        $order = $this->orderShouldBeSentToPrintful();

        foreach (self::NON_PRINTFUL_ORDER_ITEMS_DATA as $orderItemAttributes) {
            $this->helper->createOrderItem(
                array_merge($orderItemAttributes, [
                    'parent_id' => $order->id,
                ])
            );
        }

        self::assertSame(4, $order->orderItems()->count());

        $this->artisan('wmg:fulfillment pf.order');

        $orderDrops = $order->refresh()->orderDrops()->get();

        self::assertSame(1, $orderDrops->count());

        $orderDrop = $orderDrops->first();

        $order->orderItems()->sourceId('PF')->each(
            static function (OrderItem $item) use ($orderDrop) {
                self::assertEquals(
                    $orderDrop->id,
                    $item->drop_id
                );
            }
        );

        $order->orderItems()->where('source_id', '!=', 'PF')->each(
            static function (OrderItem $item) {
                self::assertNull($item->drop_id);
            }
        );
    }

    /**
     * Test that a successful order drop will send an SOAP request to Magento
     * with an Ack to Magento with status code
     * @group printful
     * @group integration
     * @group order
     * @group ordercreated
     */
    public function testCreateOrderSuccessfullySendsAckToMagento(): void
    {
        $order = $this->orderShouldBeSentToPrintful();

        foreach (self::NON_PRINTFUL_ORDER_ITEMS_DATA as $orderItemAttributes) {
            $this->helper->createOrderItem(
                array_merge($orderItemAttributes, [
                    'parent_id' => $order->id,
                ])
            );
        }

        self::assertSame(4, $order->orderItems()->count());

        $fulfillmentAckData = $this->printfulApiHelper
        ->getFulfillmentAckDataFromOrderItems(
            $order->orderItems()->sourceId('PF')->get()
        );
        $this->m1SoapClient->expects('fulfillmentAck')->with(
            Helper::M1_SOAP_TOKEN,
            $fulfillmentAckData
        )->once();

        $this->artisan('wmg:fulfillment pf.order');
        M::close();
    }

    /**
     * Test that creating an order with a failure will send a failure SOAP
     * request to Magento with X reason code
     * @group printful
     * @group integration
     * @group order
     * @group ordercreated
     * @throws Exception
     */
    public function testCreateOrderFailureSendFailureToPrintful(): void
    {
        $order = $this->orderShouldBeSentToPrintful(true);

        foreach (self::NON_PRINTFUL_ORDER_ITEMS_DATA as $orderItemAttributes) {
            $this->helper->createOrderItem(
                array_merge($orderItemAttributes, [
                    'parent_id' => $order->id,
                ])
            );
        }

        self::assertSame(4, $order->orderItems()->count());

        $fulfillmentAckData = $this->printfulApiHelper
            ->getFulfillmentAckDataFromOrderItems(
                $order->orderItems()->sourceId('PF')->get(),
                'X',
                Item::STATUS_ERROR
            );

        $this->m1SoapClient->expects('fulfillmentAck')->withArgs([
            Helper::M1_SOAP_TOKEN,
            $fulfillmentAckData
        ])->once();

        $this->artisan('wmg:fulfillment pf.order');
        M::close();
    }

    /**
     * Create an order and set expectations for order to be sent to printful
     *
     * @param bool $shouldReturnError
     * @return Order
     * @throws Exception
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function orderShouldBeSentToPrintful(
        bool $shouldReturnError = false
    ): Order {
        $this->printfulApiHelper->clearPrintfulOrders();

        $order = $this->printfulApiHelper->createPrintfulOrder(
            self::ORDER_DATA,
            self::ORDER_ADDRESS_DATA,
            self::PRINTFUL_ORDER_ITEMS_DATA
        );

        $orderArguments = $this->printfulApiHelper->getCreateOrderArguments($order);

        $call = $this->printfulApi
            ->expects('post')
            ->withArgs(
                static function ($path, $data, $params) use ($orderArguments) {
                    self::assertEqualsCanonicalizing(
                        $data,
                        $orderArguments['data'],
                        'Data in create order post request does not match expected data'
                    );

                    self::assertEqualsCanonicalizing(
                        $path,
                        $orderArguments['path'],
                        'Path that create order post request was made to does not match expected path'
                    );

                    self::assertEqualsCanonicalizing(
                        $params,
                        $orderArguments['params'],
                        'Params in create order post request does not match expected params'
                    );

                    return true;
                }
            )->andReturns(
                $shouldReturnError
                ? $this->printfulApiHelper->getCreateOrderErrorResponse()
                : $this->printfulApiHelper->getCreateOrderResponse($orderArguments)
            );


        if ($shouldReturnError) {
            $call->andThrow(new PrintfulApiException('There was an error', 400));
        }


        return $order;
    }
}
