<?php

namespace Tests\Feature\IMMuleSoft\Import\Stock;

use App\IMMuleSoft\Constants\ConfigConstant;
use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Constants\RouteConstant;
use App\IMMuleSoft\Handler\Processor\Stock;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\WarehouseTestCase;

/**
 * Class SuccessfulStockLevelImportTest
 * @package Tests\Feature\IMMuleSoft
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class SuccessfulStockLevelImportTest extends WarehouseTestCase
{
    /**
     * @var mixed
     */
    private $stockLevel;

    public function setUp(): void
    {
        parent::setUp();

        //Post Data
        $stockLevel = '[{
        "code": "953b359d-89fd-4248-84ff-dc8fa8a37dc9",
	    "sku": "TESTSKU001",
	    "ean": "1234567890123",
	    "description": "test description",
	    "quantityGood": 20,
	    "quantityQuarantined": 0,
	    "quantityDamaged": 2,
	    "quantityBlocked": 0,
	    "dateTime": "2019-11-22T10:35:15Z"
       },
       {
	    "code": "fc735664-5665-4b14-96d9-5f3a6e408f00",
	    "sku": "TESTSKU002",
	    "ean": "1234567890124",
	    "description": "test description",
	    "quantityGood": 66,
	    "quantityQuarantined": 1,
	    "quantityDamaged": 3,
	    "quantityBlocked": 1,
	    "dateTime": "2019-11-22T10:35:15Z"
       }]';

        $this->stockLevel = json_decode($stockLevel);
        $this->requestTable = 'im_mulesoft_requests';


        $this->expectedDataToBeSaved = [
            [
                'sku' => 'TESTSKU001',
                'qty' => 20,
                'source_id' => ConfigConstant::IMMULESOFT_SOURCE_ID,
            ],
            [
                'sku' => 'TESTSKU002',
                'qty' => 66,
                'source_id' => ConfigConstant::IMMULESOFT_SOURCE_ID,
            ]
        ];

        //Response message to fulfilment centre
        $this->expectResponseData = [
            "statusCode" => Response::HTTP_OK,
            "message" => ResourceConstant::RESPONSE_MESSAGE_SUCCESS,
            "messageId" => "e6ae9ff538af98795118bc8ed354d9923a804a1d",
            "resourceType"=> ResourceConstant::RESOURCE_TYPE_STOCK_LEVEL,
            "responses" => [
                [
                    'resourceCode' => '953b359d-89fd-4248-84ff-dc8fa8a37dc9',
                    'statusCode' => Response::HTTP_OK,
                    'message' => ResourceConstant::RESPONSE_MESSAGE_SUCCESS
                ],
                [
                    'resourceCode' => 'fc735664-5665-4b14-96d9-5f3a6e408f00',
                    'statusCode' => Response::HTTP_OK,
                    'message' => ResourceConstant::RESPONSE_MESSAGE_SUCCESS
                ]
            ]
        ];


        //set up basic auth user
        Artisan::call('wmg:basicauth add --username ingram --password test');

        // Get stock endpoint
        $this->url = route(RouteConstant::STOCK_LEVEL_NAME);
    }

    /**
     * successfulStockLevelImportTest
     */
    public function testImMulesoftSuccessfulStockLevelImport()
    {
        /**
         * Expected workflow
         * - Stock Level request is sent to fulfilment endpoint
         * - Endpoint will save request for offline processing
         * - Cron will pick up requests for processing
         */


        //post test data to stock endpoint using basic auth
        $response = $this->postJson(
            $this->url,
            $this->stockLevel,
            ['Authorization' => 'Basic '.base64_encode('ingram' .':'. 'test')]
        );

        $response->assertSuccessful()
            ->assertJson($this->expectResponseData);

        //check that post data was saved to request logging table
        $this->assertDatabaseCount($this->requestTable, 1);

        //check stock handler
        $mockStockProcessor = $this->getMockBuilder(Stock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateDatabase'])
            ->getMock();

        $mockStockProcessor->expects($this->atLeastOnce())
            ->method('updateDatabase')
            ->with($this->expectedDataToBeSaved)
            ->willReturn(true);

        $this->app->instance(Stock::class, $mockStockProcessor);

        Artisan::call('wmg:fulfillment immulesoft.stock');
    }
}
