<?php

namespace Tests\Unit\Mdc\Service\Event\ClientHandler;

use App\Mdc\Service\Event\ClientHandler\StockHandler;
use App\Models\Service\Model\Stock;
use App\Models\StockItem;
use App\Mdc\Clients\SoapClient;
use SoapFault;
use Tests\Unit\Mdc\MdcTestCase;
use App\Core\Services\EventService;
use App\Models\Service\Event\RequestData\TokenRequest;

/**
 * Test handling stock update for MDC
 *
 * Class StockHandlerTest
 * @category WMG
 * @package  Tests\Unit\Mdc\Service\Event\ClientHandler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class StockHandlerTest extends MdcTestCase
{
    public function testStockUpdate()
    {
        $result = null;
        $soapClientMock = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['stockSetMulti'])
            ->getMock();
        $soapClientMock->method('stockSetMulti')
            ->willReturn($result);

        $stockHandler = $this->app->make(StockHandler::class);
        $eventCall = $this->createStockEventCall();
        $request = new TokenRequest('', $eventCall->getData());
        $response = $stockHandler->handle(EventService::EVENT_SOURCE_UPDATE, $request, $soapClientMock);
        $this->assertEquals($result, $response);
    }

    public function testStockUpdateWithConnectionError()
    {
        StockItem::factory()->create(['source_id' => 'US', 'sku' => '075678654039']);
        $soapClientMock = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['stockSetMulti'])
            ->getMock();
        $soapClientMock->method('stockSetMulti')
            ->will($this->throwException(new SoapFault('Server', 'Request error')));
        $stockHandler = $this->app->make(StockHandler::class);
        $eventCall = $this->createStockEventCall();
        $request = new TokenRequest('', $eventCall->getData());
        $this->expectException(SoapFault::class);
        $stockHandler->handle(EventService::EVENT_SOURCE_UPDATE, $request, $soapClientMock);
    }


    private function createStockEventCall()
    {
        $stock = new Stock();
        $stockList = [
            [
                'sku' => '075678654039',
                'qty' => 50,
            ],
            [
                'sku' => '075678654022',
                'qty' => 20,
            ],
            [
                'sku' => '090317433608',
                'qty' => 10,
            ]
        ];
        $snapShot = $stock->newSnapshot();
        $snapShot->sourceId = 'US';
        foreach ($stockList as $stockData) {
            $snapShot->newStockItem($stockData['sku'], $stockData['qty']);
        }
        $eventCall = $this->createServiceEventCall(
            EventService::EVENT_SOURCE_UPDATE,
            $stock
        );

        return $eventCall;
    }
}
