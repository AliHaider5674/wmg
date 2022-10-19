<?php

namespace Tests\Feature\Mdc;

use App\Core\Handlers\StockExportHandler;
use App\Core\Services\ClientService;
use App\Mdc\Clients\SoapClient;
use App\Mdc\Constants\ConfigConstant;
use App\Models\StockItem;
use App\User;
use Tests\TestCase;
use App\Services\WarehouseService;
use WMGCore\Services\ConfigService;
use Mockery as M;

/**
 * Test stock import
 *
 * Class StockImportTest
 * @category WMG
 * @package  Tests\Feature\Mdc
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockImportTest extends TestCase
{
    protected $warehouseService;
    protected $soapClient;
    /** @var ConfigService  */
    private $configService;
    public function setUp(): void
    {
        parent::setUp();
        /** @var WarehouseService $warehouseService */
        $this->warehouseService = app()->make(WarehouseService::class);
        /** @var ClientService $clientManager */
        $this->soapClient = M::mock(SoapClient::class);
        $this->soapClient->shouldReceive('config');
        $this->soapClient->shouldReceive('setToken');
        $this->soapClient->shouldReceive('newToken')->andReturn('123');
        $this->soapClient->shouldReceive('getToken')->andReturn('123');
        //For some reason instance would create original class
        $this->app->bind(SoapClient::class, function () {
            return  $this->soapClient;
        });

        $user = User::factory()->create();
        $service = [
            "app_id" => "m1",
            "name" => "m1",
            "client" => "m1",
            "events" => ["*"],
            "event_rules" => [],
            "addition" => [
                "wsdl" => "http://localhost"
            ]
        ];
        $response = $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);
        $response->assertStatus(200);
        $this->configService = $this->app->make(ConfigService::class);
    }

    /**
     * Test stock import
     *
     * @param $createStockSourceIds
     * @param $mdcSourceIds
     * @param $allowedOverlap
     * @param $expectCallCount
     * @SuppressWarnings(PHPMD)
     * @return void
     * @testWith ["GNAR,US","US",0,0]
     *           ["GNAR","US",0,0]
     *           ["GNAR,US","US",1,1]
     *           ["US","US",0,1]
     */
    public function testStockImport($createStockSourceIds, $mdcSourceIds, $allowedOverlap, $expectCallCount)
    {
        $mdcSourceIds = explode(',', $mdcSourceIds);
        $createStockSourceIds = explode(',', $createStockSourceIds);
        $this->configService->update(ConfigConstant::MDC_STOCK_SOURCE_IDS, $mdcSourceIds);
        $this->configService->update(ConfigConstant::MDC_ALLOW_STOCK_SOURCE_OVERLAP, $allowedOverlap);
        $sku = 'sku123';
        foreach ($createStockSourceIds as $id) {
            StockItem::factory()->create(['source_id' => $id, 'sku' => $sku]);
        }

        $stockExportHandler = $this->app->make(StockExportHandler::class);
        $this->warehouseService->callHandler($stockExportHandler);
        //tmp disable this test cases
//        if ($expectCallCount == 0) {
//            $this->soapClient->expects('stockSetMulti')->never();
//        } else {
//            $this->soapClient->expects('stockSetMulti');
//        }
    }
}
