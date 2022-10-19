<?php

namespace Tests\Feature\Mom\Warehouse;

use App\Core\Services\ClientService;
use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Models\Order;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\MES\Handler\AckHandler;
use App\Mom\Helpers\ReasonCodeHelper;
use App\Mom\Models\Service\Event\ClientHandler\DefaultHandler;
use App\Mom\Models\Service\Event\ClientHandler\LineChangeHandler;
use App\Mom\Models\Service\Event\ClientHandler\OrderActionCreatedHandler;
use App\Mom\Models\Service\Event\EventMap;
use App\Mom\Models\Service\Event\MomClient;
use App\Core\Services\EventService;
use App\Models\OrderItem;
use App\OrderAction\Models\OrderAction;
use App\User;
use Illuminate\Support\Arr;
use Tests\Feature\Mom\HistoryClient;
use Tests\TestCase;
use App\Models\Service\Model\ShipmentLineChange\Item as ShipmentLineItem;

/**
 * Test receive different status code from warehouse
 *
 * Class StatusReceivedTest
 * @category WMG
 * @package  Tests\Feature\Mom\Warehouse
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @group    statusCodeHandling
 * @group    mom
 * @testdox  Test receive different reason code and request to MOM
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemStatusReceivedTest extends TestCase
{
    const DEFAULT_SALES_CHANNEL = 'STG-Bruno-Mars';
    /** @var MomClient */
    protected $momClient;
    /** @var HistoryClient */
    protected $historyClient;
    /** @var  EventService*/
    protected $eventManager;
    /** @var ReasonCodeHelper */
    protected $reasonCodeHelper;

    public function setUp():void
    {
        parent::setUp();
        $this->historyClient = new HistoryClient();
        /** @var ClientService $clientManager */
        $clientManager = $this->app->make(ClientService::class);
        $eventMap = $this->app->make(EventMap::class);
        $momClient = new MomClient(
            $this->historyClient,
            $eventMap,
            [
                $this->app->make(LineChangeHandler::class),
                $this->app->make(OrderActionCreatedHandler::class),
                $this->app->make(DefaultHandler::class),
            ],
            $this->app->make(NetworkClientService::class)
        );

        $clientManager->addClient($momClient);

        $this->app->instance(MomClient::class, $momClient);

        $this->eventManager = app()->make(EventService::class);
        $this->reasonCodeHelper = app()->make(ReasonCodeHelper::class);
        //create service
        $user = User::factory()->create();

        $service = [
            "app_id" => "m1",
            "name" => "m1",
            "client" => "m1",
            "events" => ["*"],
            "event_rules" => [
                'sales_channel' => '^M113US'
            ],
            "addition" => [
            ]
        ];

        $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);

        $service = [
            "app_id" => "mom",
            "name" => "mom",
            "client" => "mom",
            "events" => ["*"],
            "event_rules" => [
                'sales_channel' => '^(?!M113US|M113EU)'
            ],
            "addition" => [
            ]
        ];
        $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);
    }

    /**
     * Test
     * @testWith ["M113US", false]
     *           ["M113EU", false]
     *           ["BRUNOMARS", true]
     *           ["", true]
     * @testdox Reason code A in sales channel "$salesChannel" -> order action created: $expect
     * @param $salesChannel
     * @param $expect
     * @return void
     * @throws \Exception
     */
    public function testSalesChannelOrderActionCreation($salesChannel, $expect)
    {
        $detail = $this->createReceive(['A'], function ($order) use ($salesChannel) {
            $order->setAttribute('sales_channel', $salesChannel);
            $order->save();
        });
        $order = $detail['order'];
        $orderAction = OrderAction::where('order_id', $order->getAttribute('order_id'))
            ->where('sales_channel', $order->getAttribute('sales_channel'))
            ->first();
        $this->assertEquals($expect, $orderAction ? true : false);
    }

    /**
     * Test receive reason code 2,A,B
     * @testdox Code $reasonCode -> onhold, backorder and add comment
     * @testWith ["A"]
     *           ["B"]
     *           ["2"]
     *
     * @param    String $reasonCode
     *
     * @return   void
     * @throws \Exception
     */
    public function testReceivedOnHoldStatusCode(String $reasonCode)
    {
        $detail = $this->createReceive([$reasonCode]);

        //Ensure order action created
        $orderAction = OrderAction::where('order_id', $detail['order']->getAttribute('order_id'))
            ->where('sales_channel', self::DEFAULT_SALES_CHANNEL)
            ->first();
        $this->assertNotNull($orderAction);

        $requests = $this->historyClient->getRequests();

        $this->assertEquals(2, count($requests), 'Ensure there are only two requests to MOM.');
        $request = array_shift($requests);
        $item = $detail['items']->shift();
        $this->validateCommentRequest(
            $request,
            $reasonCode,
            $item,
            'Received %s(%s-%s), put order to "On Hold" in Fulfillment.'
        );
        $request = array_shift($requests);
        $this->validateLineChangeRequest($request, ShipmentLineItem::STATUS_PICK_DECLINED);
    }

    /**
     * Test receive reason code 3, 4, 5, M
     * @testdox  Code $reasonCode -> add comment and backorder
     * @testWith ["3"]
     *           ["4"]
     *           ["5"]
     *           ["M"]
     * @param    String $reasonCode
     * @return void
     * @throws \Exception
     */
    public function testReceivedBackorderStatusCode($reasonCode)
    {
        $detail = $this->createReceive([$reasonCode]);

        //Ensure no order action created
        $orderAction = OrderAction::where('order_id', $detail['order']->getAttribute('order_id'))
            ->where('sales_channel', 'test')
            ->first();
        $this->assertNull($orderAction);

        $requests = $this->historyClient->getRequests();

        $this->assertEquals(2, count($requests), 'Ensure there are only one requests to MOM.');
        $request = array_shift($requests);
        $item = $detail['items']->shift();
        $this->validateCommentRequest($request, $reasonCode, $item, 'Received %s(%s-%s)');
        $request = array_shift($requests);
        $this->validateLineChangeRequest($request, ShipmentLineItem::STATUS_PICK_DECLINED);
    }

    /**
     * Test standard or unknown reason code
     * @testdox  Code $reasonCode from MES -> mark item received in MOM
     * @testWith [null]
     *           [""]
     *           ["*"]
     * @param    String $reasonCode
     * @return void
     * @throws \Exception
     */
    public function testReceivedStandard($reasonCode)
    {
        $detail = $this->createReceive([$reasonCode]);

        //Ensure no order action created
        $orderAction = OrderAction::where('order_id', $detail['order']->getAttribute('order_id'))
            ->where('sales_channel', self::DEFAULT_SALES_CHANNEL)
            ->first();
        $this->assertNull($orderAction);

        $requests = $this->historyClient->getRequests();
        $this->assertEquals(2, count($requests), 'Ensure there are only one requests to MOM.');
        $request = array_shift($requests);
        $item = $detail['items']->shift();
        $this->validateCommentRequest($request, $reasonCode, $item, 'Warehouse received %s');
        $request = array_shift($requests);
        $this->validateLineChangeRequest($request, ShipmentLineItem::STATUS_RECEIVED_BY_LOGISTICS);
    }

    /**
     * Receive multiple reason codes
     * @testdox  Codes 3, A and null at once -> handle them in right status
     * @return void
     * @throws \Exception
     */
    public function testReceiveMultipleReasonCodes()
    {
        $reasonCodes = ['A' , '3' , null];
        $detail = $this->createReceive($reasonCodes);
        $items = $detail['items'];
        $requests = $this->historyClient->getRequests();
        //Ensure order action created
        $orderAction = OrderAction::where('order_id', $detail['order']->getAttribute('order_id'))
            ->where('sales_channel', self::DEFAULT_SALES_CHANNEL)
            ->first();
        $this->assertNotNull($orderAction, 'Order action created');

        $this->assertEquals(6, count($requests), 'Ensure there are 5 requests to MOM.');

        //A
        $request = array_shift($requests);
        $reasonCode = array_shift($reasonCodes);
        $item = $items->shift();
        $this->validateCommentRequest(
            $request,
            $reasonCode,
            $item,
            'Received %s(%s-%s), put order to "On Hold" in Fulfillment.'
        );
        $request = array_shift($requests);
        $this->validateLineChangeRequest($request, ShipmentLineItem::STATUS_PICK_DECLINED);

        //3
        $request = array_shift($requests);
        $reasonCode = array_shift($reasonCodes);
        $item = $items->shift();
        $this->validateCommentRequest(
            $request,
            $reasonCode,
            $item,
            'Received %s(%s-%s)'
        );
        $request = array_shift($requests);
        $this->validateLineChangeRequest($request, ShipmentLineItem::STATUS_PICK_DECLINED);

        //null
        $request = array_shift($requests);
        $reasonCode = array_shift($reasonCodes);
        $item = $items->shift();
        $this->validateCommentRequest($request, $reasonCode, $item, 'Warehouse received %s');
        $request = array_shift($requests);
        $this->validateLineChangeRequest($request, ShipmentLineItem::STATUS_RECEIVED_BY_LOGISTICS);

        $this->assertEmpty($requests, 'Checked all requests');
    }

    /**
     * Validate line change request to MOM
     * @param array  $request
     * @param String $status
     *
     * @return void
     */
    private function validateLineChangeRequest(array $request, String $status)
    {
        $this->assertEquals(
            'magento.logistics.shipment_request_management.lines_change_status',
            Arr::get($request, 'method'),
            'Send order line status change to MOM'
        );
        $this->assertEquals(
            $status,
            Arr::get($request, 'params.items.0.status'),
            'Send order line status change to MOM'
        );
    }

    /**
     * Validate a MOM comment request
     *
     * @param array                 $request
     * @param string                $reasonCode
     * @param \App\Models\OrderItem $item
     * @param string                $template
     *
     * @return void
     */
    private function validateCommentRequest(
        array $request,
        $reasonCode,
        OrderItem $item,
        $template = 'Received %s(%s-%s)'
    ) {
        $this->assertEquals(
            'magento.sales.order_management.create_comment',
            $request['method'],
            'Send comment creation command to MOM'
        );

        $expectMessage = sprintf(
            $template,
            $item->sku,
            $reasonCode,
            $this->reasonCodeHelper->getStatusCodeName($reasonCode)
        );
        $this->assertEquals(
            $expectMessage,
            Arr::get($request, 'params.order_comment.comment'),
            'Send comment creation command to MOM'
        );
    }


    /**
     * Create a warehouse receive by given reason codes
     *
     * @param  array $reasonCodes
     * @param  $preprocessCallback
     * @return array
     * @throws \Exception
     */
    private function createReceive(array $reasonCodes, $preprocessCallback = null)
    {

        $order = Order::factory()->create([
            'sales_channel' => self::DEFAULT_SALES_CHANNEL,
            'order_id' => 'TEST123']);
        $orderItems = OrderItem::factory()->count(count($reasonCodes))
            ->make(['item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL]);
        $order->orderItems()->saveMany($orderItems);

        /** @var AckHandler $ackHandler */
        $ackHandler = app()->make(AckHandler::class);
        $buildParameter = new ShipmentLineChangeParameter();
        $buildParameter->orderId = $order->id;
        $count = 0;
        foreach ($orderItems as $orderItem) {
            $item = new ItemParameter();
            $item->orderItemId = $orderItem->id;
            $item->sku = $orderItem->sku;
            $item->quantity = $orderItem->quantity;
            $item->backOrderReasonCode = $reasonCodes[$count];
            $buildParameter->addItem($item);
            $count++;
        }
        if ($preprocessCallback) {
            call_user_func_array($preprocessCallback, [$order, $orderItems]);
        }
        $ackHandler->processAckParameter($buildParameter);
        return ['order' => $order, 'items' => $orderItems];
    }
}
