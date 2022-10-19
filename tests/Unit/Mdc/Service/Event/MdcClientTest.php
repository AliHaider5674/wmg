<?php

namespace Tests\Unit\Mdc\Service\Event;

use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Mdc\Service\Event\SoapClientManager;
use App\Core\Services\EventService;
use App\Mdc\Service\Event\MdcClient;
use App\Mdc\Service\SoapFaultErrorParser;
use App\Core\ServiceEvent\TokenProvider;
use App\Services\Token\TokenDbCache;
use App\Mdc\Service\Event\ClientHandler\ShipmentHandler;
use App\Models\Service\Model\Shipment;
use SoapFault;
use Tests\Unit\Mdc\MdcTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Mdc\Clients\SoapClient;

/**
 * Test Mdc client to send event to MDC
 *
 * Class MdcClientTest
 * @category WMG
 * @package  Tests\Unit\Mdc\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class MdcClientTest extends MdcTestCase
{
    /**
     * @var MdcClient
     */
    private MdcClient $mdcClient;

    /**
     * @var string
     */
    private string $token;

    /**
     * @var MockObject|SoapClient
     */
    private SoapClient $soapClientMock;

    public function setUp():void
    {
        parent::setUp();
        $this->token = 'THIS IS A SOAP SESSION TOKEN';
        $this->soapClientMock = $this->getMockBuilder(SoapClient::class)
                          ->disableOriginalConstructor()
                          ->addMethods([
                              'login',
                              'salesOrderShipmentCreate',
                              'salesOrderShipmentAddTrack',
                              'salesOrderShipmentSendInfo',
                              'salesOrderInfo'
                          ])
                          ->getMock();
        $soapClientManagerMock = $this->getMockBuilder(SoapClientManager::class)
                                ->onlyMethods([
                                    'getClient'
                                ])
                                ->getMock();

        $soapClientManagerMock->method('getClient')
            ->willReturn($this->soapClientMock);


        $this->soapClientMock
            ->method('login')
            ->willReturn($this->token);

        $this->soapClientMock
            ->method('salesOrderInfo')
            ->willReturn((object)['items'=>[]]);

        $this->soapClientMock
            ->method('salesOrderShipmentCreate')
            ->willReturn('1234567890');


        $this->soapClientMock
            ->method('salesOrderShipmentAddTrack')
            ->willReturn('1111111111');

        $this->soapClientMock
            ->method('salesOrderShipmentSendInfo')
            ->willReturn(null);

        $tokenDbCache = new TokenDbCache();
        $tokenProvider = new TokenProvider($tokenDbCache);
        $shipmentHandler = new ShipmentHandler();
        $errorParser = new SoapFaultErrorParser();
        $this->mdcClient = new MdcClient(
            $tokenProvider,
            $soapClientManagerMock,
            $errorParser,
            [
                $shipmentHandler
            ],
            $this->app->make(NetworkClientService::class)
        );
    }

    public function testCalls()
    {
        $eventCall = $this->createShipmentServiceEventCall();
        $shipmentIds = $this->mdcClient->request($eventCall);
        $this->assertEquals([ShipmentHandler::class => ['1234567890']], $shipmentIds);
    }

    public function testIncorrectLogin()
    {
        $this->soapClientMock->expects($this->any())
            ->method('login')
            ->will($this->throwException(new SoapFault('5', 'Unauthorized')));
        $eventCall = $this->createShipmentServiceEventCall();
        $this->expectException(SoapFault::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->mdcClient->request($eventCall);
    }

    public function testIncorrectLoginRetry()
    {
        $this->soapClientMock->expects($this->any())
            ->method('login')
            ->will($this->onConsecutiveCalls([
                $this->throwException(new SoapFault('5', 'Unauthorized')),
                $this->returnValue((object) [$this->token]),
            ]));

        $eventCall = $this->createShipmentServiceEventCall();
        $shipmentId = $this->mdcClient->request($eventCall);
        $this->assertEquals([ShipmentHandler::class => ['1234567890']], $shipmentId);
    }

    protected function createShipmentServiceEventCall()
    {
        $eventName = EventService::EVENT_ITEM_SHIPPED;
        $shipment = new Shipment();
        $package = $shipment->newPackage();
        $package->carrier = 'USPS';
        $package->trackingNumber = '1234567';
        $package->trackingLink = 'https://test/';
        $item = $package->newAggregatedItem('abc');
        $item->quantity = 5;
        $item->addHiddenLineQtyMap(1, 5);
        return parent::createServiceEventCall($eventName, $shipment);
    }
}
