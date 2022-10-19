<?php

namespace Tests\Feature\IM;

use App\Core\Services\ClientService;
use App\Mdc\Clients\SoapClient;
use App\Mdc\Service\Event\MdcClient;
use App\Models\ServiceEventCall;
use App\Services\WarehouseService;
use App\IM\Handler\IO\ApiStock;
use App\IM\Handler\StockHandler;
use App\Models\Service;
use App\Models\ServiceEvent;
use App\Models\Service\Model\Stock;
use App\Models\Service\Model\Stock\StockItem;
use Tests\Feature\WarehouseTestCase;
use Mockery as M;
use App\Mdc\Service\Event\SoapClientManager;

/**
 * Class ApiStockImportTest
 * @category WMG
 * @package  Tests\Feature\IM
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApiStockImportTest extends WarehouseTestCase
{
    const TEST_WAREHOUSE_HANDLER = 'apiStock';

    const SOURCE_UPDATE_ATTRIBUTE_SNAPSHOT = 'snapshot';
    const SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_SOURCE_ID = 'sourceId';
    const SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_MODE = 'mode';
    const SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_CREATED = 'createdOn';
    const SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_STOCK = 'stock';
    const SOURCE_UPDATE_ATTRIBUTE_STOCK_SKU = 'sku';
    const SOURCE_UPDATE_ATTRIBUTE_STOCK_QUANTITY = 'quantity';

    /**
     * @var WarehouseService
     */
    protected $warehouseService;

    protected $service;

    /**
     * list of sku used to test stock import against
     * @var array
     */
    private $skus = [
        "0010467410823","0030633337921","0601811170526"
    ];


    /**
     * setUp
     */
    public function setUp():void
    {
        parent::setUp();
        $this->warehouseService = app()->make(WarehouseService::class);

        //Generate test service

        $this->service = Service::factory()->count(1)->create([
            'status'=> 1
        ])->each(fn(Service $service) => $service->events()->save(
            ServiceEvent::factory()->make([
                'event' => 'source.update',
                'status'=> 1,
            ])
        ));
    }

    /**
     * getMockResponse
     * Get mock response from Warehouse
     * @return array
     */
    protected function getMockResponse()
    {
        $response = array();

        foreach ($this->skus as $sku) {
            $importLine = array();

            $importLine['SKU'] = $sku;
            $importLine['QuantityAvailable'] = rand(0, 1000);
            $importLine['QuantityOnShelf'] = rand(0, 1000);
            $importLine['QuantityDamaged'] = rand(0, 1000);
            $importLine['QuantityQuarantined'] = rand(0, 1000);

            $response[] = $importLine;
        };

        return $response;
    }


    /**
     * setupMockEnvironment
     *
     * @param $response
     */
    protected function getMockHandler($response)
    {
        $apiIoMock = $this->getMockBuilder(ApiStock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataFromWarehouse'])
            ->getMock();

        $apiIoMock->method('getDataFromWarehouse')
            ->willReturn($response);

        return $this->app->make(
            StockHandler::class,
            ['ioAdapter' => $apiIoMock]
        );
    }

    /**
     * testSuccessfulStockImport
     */
    public function testSuccessfulStockImport()
    {
        $soapClientManager = M::mock(SoapClientManager::class);
        $soapClient = M::mock(SoapClient::class);

        $soapClientManager->shouldReceive('getClient')
            ->withAnyArgs()
            ->andReturns($soapClient);

        /** @var MdcClient $mdcClient */
        $mdcClient = $this->app->make(MdcClient::class, [
            'soapClientManager' => $soapClientManager
        ]);

        /** @var ClientService $clientService */
        $clientService = $this->app->make(ClientService::class);
        $clientService->addClient($mdcClient);

        //kick off stock import process
        $this->warehouseService->callHandler(
            $this->getMockHandler($this->getMockResponse())
        );

        //verify service call data
        //first load service event from service
        //filter on status, and source_id (warehouse), use where exists to filter out duplicate order_id
        $serviceEvent = ServiceEvent::where(
            'parent_id',
            $this->service->pluck('id')->toArray()
        )->first();

        $serviceEventCalls = ServiceEventCall::where('parent_id', $serviceEvent->id)->get();

        foreach ($serviceEventCalls as $serviceCalls) {
            $data = unserialize($serviceCalls->data);

            $this->assertInstanceOf(Stock::class, $data, 'service call data instance of stock');

            $this->assertObjectHasAttribute(
                self::SOURCE_UPDATE_ATTRIBUTE_SNAPSHOT,
                $data,
                'service call has a' .  self::SOURCE_UPDATE_ATTRIBUTE_SNAPSHOT
            );

            $this->assertObjectHasAttribute(
                self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_SOURCE_ID,
                $data->snapshot,
                'service call snapshot has attribute' . self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_SOURCE_ID
            );

            $this->assertObjectHasAttribute(
                self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_MODE,
                $data->snapshot,
                "service call snapshot has attribute" . self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_MODE
            );

            $this->assertObjectHasAttribute(
                self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_CREATED,
                $data->snapshot,
                "service call snapshot has attribute" . self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_CREATED
            );

            $this->assertObjectHasAttribute(
                self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_STOCK,
                $data->snapshot,
                "service call snapshot has attribute" . self::SOURCE_UPDATE_SNAPSHOT_ATTRIBUTE_STOCK
            );

            $this->assertIsArray($data->snapshot->stock, 'Snapshot stock is an Array');

            foreach ($data->snapshot->stock as $stock) {
                $this->assertInstanceOf(StockItem::class, $stock, 'Snapshot stock is istance of StockItem');

                $this->assertObjectHasAttribute(
                    self::SOURCE_UPDATE_ATTRIBUTE_STOCK_SKU,
                    $stock,
                    "service call snapshot stock has attribute" . self::SOURCE_UPDATE_ATTRIBUTE_STOCK_SKU
                );

                $this->assertObjectHasAttribute(
                    self::SOURCE_UPDATE_ATTRIBUTE_STOCK_QUANTITY,
                    $stock,
                    "service call snapshot stock has attribute" . self::SOURCE_UPDATE_ATTRIBUTE_STOCK_QUANTITY
                );
            }
        }
    }
}
