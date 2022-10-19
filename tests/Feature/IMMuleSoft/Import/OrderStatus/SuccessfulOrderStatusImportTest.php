<?php

namespace Tests\Feature\IMMuleSoft\Import\OrderStatus;

use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Constants\RouteConstant;
use App\IMMuleSoft\Models\Weight\ItemWeightCalculator;
use App\Services\ShipmentProcessor;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\WarehouseTestCase;
use App\IMMuleSoft\Handler\Processor\OrderStatus\Shipment;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use Mockery as M;

/**
 * Class SuccessfulOrderStatusImportTest
 * @package Tests\Feature\IMMuleSoft\Import\Stock
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class SuccessfulOrderStatusImportTest extends WarehouseTestCase
{
    /**
     * @var mixed
     */
    private $orderStatuses;

    public function setUp(): void
    {
        parent::setUp();

        $requestPostBody = $this->getRequestPostBody();

        $this->orderStatuses = json_decode($requestPostBody);
        $this->requestTable = 'im_mulesoft_requests';

        //Response message to fulfilment centre
        $this->expectResponseData = [
            "statusCode" => Response::HTTP_OK,
            "message" => ResourceConstant::RESPONSE_MESSAGE_SUCCESS,
            "messageId" => "17b717c607e53b0ffbdc54bf10f34abadd4fff26",
            "resourceType"=> ResourceConstant::RESOURCE_TYPE_SALES_ORDER_STATUS,
            "responses" => [
                [
                    'resourceCode' => "adfd5-448e-b845-16a1923dbc26",
                    'statusCode' => Response::HTTP_OK,
                    'message' => ResourceConstant::RESPONSE_MESSAGE_SUCCESS
                ]
            ]
        ];


        //set up basic auth user
        Artisan::call('wmg:basicauth add --username ingram --password test');

        // Get stock endpoint
        $this->url = route(RouteConstant::ORDER_STATUS_NAME);
    }

    /**
     * testImMulesoftSuccessfulOrderStatusImport
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function testImMulesoftSuccessfulOrderStatusImport()
    {
        /**
         * Expected workflow
         * - Order Statuses request is sent to fulfilment endpoint
         * - Endpoint will save request for offline processing
         * - Cron will pick up requests for processing
         */


        //post test data to order status endpoint using basic auth
        $response = $this->postJson(
            $this->url,
            $this->orderStatuses,
            ['Authorization' => 'Basic '.base64_encode('ingram' .':'. 'test')]
        );

        $response->assertSuccessful()
            ->assertJson($this->expectResponseData);

        //check that post data was saved to request logging table
        $this->assertDatabaseCount($this->requestTable, 1);

        $shipmentProcessor = $this->app->make(ShipmentProcessor::class);
        $shipmentWeightCalculator = $this->app->make(ItemWeightCalculator::class);

        $shipment = M::mock(
            Shipment::class,
            [$shipmentProcessor, $shipmentWeightCalculator]
        )->makePartial();

        $shipment
            ->shouldReceive('processParameters')->withArgs(
                function ($parameters) {
                    try {
                        $shipmentParameter = $parameters[0];
                        $this->assertInstanceOf(ShipmentParameter::class, $shipmentParameter);
                        $this->assertObjectHasAttribute('orderId', $shipmentParameter);
                        $this->assertObjectHasAttribute('packages', $shipmentParameter);
                    } catch (Exception $e) {
                        die($e->getMessage());
                    }
                    return true;
                }
            )->andReturn(true);

        $this->app->instance(Shipment::class, $shipment);

        Artisan::call('wmg:fulfillment immulesoft.order.status');
    }

    /**
     * getRequestPostBody
     * @return string
     */
    private function getRequestPostBody()
    {
        return '[{
		"code": "adfd5-448e-b845-16a1923dbc26",
		"orderCode": "D0000012748SF_EU",
		"orderConsumerCode": "158094",
		"orderPortalCode": 472698,
		"salesChannelCode": "default",
		"status": "Complete",
		"addresses": [
			{
				"type": "shipment",
				"firstName": "Deepika",
				"lastName": "P",
				"fullName": "Deepika P",
				"addressLine1": "22 Portman Square",
				"postalCode": "W1H 7BG",
				"city": "London",
				"state": "England",
				"countryCode": "gb",
				"phoneNumber1": "1234567890",
				"email": "deepika.palleboina@wmg.com"
			},
			{
				"type": "invoice",
				"firstName": "Deepika",
				"lastName": "P",
				"fullName": "Deepika P",
				"addressLine1": "22 Portman Square",
				"postalCode": "W1H 7BG",
				"city": "London",
				"state": "England",
				"countryCode": "gb",
				"phoneNumber1": "1234567890",
				"email": "deepika.palleboina@wmg.com"
			}
		],
		"shipments": [
			{
				"code": "13693",
				"shippingDate": "2022-07-13",
				"expectedDelivery": {
					"date": "2022-07-14"
				},
				"carrier": {
					"code": "5",
					"name": "DPD",
					"serviceCode": "1",
					"serviceName": "DPD 2Day"
				},
				"wmsReference": "13693",
				"shipmentLines": [
					{
						"orderLineNumber": 64278,
						"sku": "0093624881254",
						"quantity": 1
					}
				],
				"parcels": [
					{
						"trackingCode": "15501983000062",
						"statusDateTime": "2022-07-13T13:59:29Z"
					}
				]
			}
		],
		"salesOrderLines": [
			{
				"lineNumber": 64278,
				"sku": "0093624881254",
				"description": "The BBC Sessions CD",
				"quantityOrdered": 1,
				"quantityProcessing": 0,
				"quantityShipped": 1,
				"quantityCancelled": 0,
				"quantityBackorder": 0
			}
		]
	}]';
    }
}
