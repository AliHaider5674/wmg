<?php

namespace Tests\Feature\IMMuleSoft\Import\ProductDataUpdate;

use App\Catalog\Models\ProductDimension;
use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Constants\RouteConstant;
use App\IMMuleSoft\Handler\Processor\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\WarehouseTestCase;

/**
 * Class SuccessfulProductDataUpdateImportTest
 * @package Tests\Feature\IMMuleSoft\Import\ProductDataUpdate
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class SuccessfulProductDataUpdateImportTest extends WarehouseTestCase
{
    /**
     * @var mixed
     */
    private $productDataUpdate;

    public function setUp(): void
    {
        parent::setUp();

        //Post Data
        $productDataUpdate = '[
          {
            "code": "7764936e-3053-4d9a-b1ad-4cdc70c7125c",
            "sku": "0075678638497",
            "weight": 150,
            "length": 1,
            "width": 14,
            "height": 11,
            "isFragile": false
          },
          {
            "code": "1171b6e2-5141-451d-a186-306d5e87c146",
            "sku": "0190296742132",
            "weight": 2000,
            "length": 200,
            "width": 100,
            "height": 100,
            "isFragile": false
          }
        ]';

        $this->productDataUpdate = json_decode($productDataUpdate);
        $this->requestTable = 'im_mulesoft_requests';


        $this->expectedDimensionData = [
            [
                'sku' => "0075678638497",
                'type' => 'weight',
                'unit' => 'g',
                'value' => 150.0,
            ],
            [
                'sku' => "0190296742132",
                'type' => 'weight',
                'unit' => 'g',
                'value' => 2000.0,
            ]
        ];

        //Response message to fulfilment centre
        $this->expectResponseData = [
            "statusCode" => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            "message" => ResourceConstant::RESPONSE_MESSAGE_SUCCESS,
            "messageId" => "a544b267f6e594dacde8d0b666de4ae2bf77cc1e",
            "resourceType"=> ResourceConstant::RESOURCE_TYPE_PRODUCT_DATA_UPDATE,
            "responses" => [
                [
                    'resourceCode' => '7764936e-3053-4d9a-b1ad-4cdc70c7125c',
                    'statusCode' => Response::HTTP_OK,
                    'message' => ResourceConstant::RESPONSE_MESSAGE_SUCCESS
                ],
                [
                    'resourceCode' => '1171b6e2-5141-451d-a186-306d5e87c146',
                    'statusCode' => Response::HTTP_OK,
                    'message' => ResourceConstant::RESPONSE_MESSAGE_SUCCESS
                ]
            ]
        ];

        //set up basic auth user
        Artisan::call('wmg:basicauth add --username ingram --password test');

        // Get stock endpoint
        $this->url = route(RouteConstant::PRODUCT_NAME);
    }

    /**
     * successfulStockLevelImportTest
     */
    public function testImMulesoftSuccessfulProductDataUpdateImport()
    {
        /**
         * Expected workflow
         * - Product Data Update request is sent to fulfilment endpoint
         * - Endpoint will save request for offline processing
         * - Cron will pick up requests for processing
         */

        //post test data to stock endpoint using basic auth
        $response = $this->postJson(
            $this->url,
            $this->productDataUpdate,
            ['Authorization' => 'Basic ' . base64_encode('ingram' . ':' . 'test')]
        );

        $response->assertSuccessful()
            ->assertJson($this->expectResponseData);

        //check that post data was saved
        $this->assertDatabaseCount($this->requestTable, 1);

        Artisan::call('wmg:fulfillment immulesoft.product');

        //Check product record was correctly saved.
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', [
            'sku' => '0075678638497',
        ]);
        $this->assertDatabaseHas('products', [
            'sku' => '0190296742132',
        ]);

        //Check product dimension was correctly saved.
        $productDimensions = ProductDimension::query()->get()->all();

        $actualDimensionRecords = array();

        foreach ($productDimensions as $dimension) {
            $actualDimensionRecords[] =
                [
                    'sku' => $dimension->product_sku,
                    'type' => $dimension->type,
                    'unit' => $dimension->unit,
                    'value' => (double) $dimension->value,
                ];
        }

        $this->assertEquals($this->expectedDimensionData, $actualDimensionRecords);
    }
}
