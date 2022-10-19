<?php

namespace Tests\Unit\MES\Handler\IO\Shipment;

use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use Illuminate\Support\Arr;
use WMGCore\Services\ConfigService;
use Tests\TestCase;
use App\MES\Handler\Helper\BackOrderHelper;
use App\MES\Handler\IO\Shipment\Tracker;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\MES\Handler\IO\FlatOrder;

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
class TrackerTest extends TestCase
{
    /** @var Tracker */
    private $shipmentTracker;
    private $sampleOrderData;
    private $sampleItemData;
    private $configServiceMock;
    public function setUp():void
    {
        $helper = $this->getMockBuilder(BackOrderHelper::class)
            ->onlyMethods(['isBackOrder'])
            ->getMock();
        $this->configServiceMock = $this->getMockBuilder(ConfigService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getJson'])
            ->getMock();
        $helper->method('isBackOrder')
            ->will($this->returnValueMap([
                ['4', true],
                ['5', false]
            ]));
        $this->configServiceMock->method('getJson')->willReturn([
            [
                'exp' => '^UPS',
                'carrier' => 'UPS'
            ]
        ]);
        $this->shipmentTracker = new Tracker($helper, $this->configServiceMock);
        $this->resetSampleData();
    }

    /**
     * Test an order is fully shipped
     *
     * @return void
     * @throws \App\Exceptions\RecordExistException
     */
    public function testAddShippedItems()
    {
        //Add all ship
        $this->resetSampleData();
        $this->shipmentTracker->addOrder($this->sampleOrderData);
        $this->shipmentTracker->addItem($this->sampleItemData);
        $this->assertEquals(1, $this->shipmentTracker->count());
        $parameter = $this->shipmentTracker->getIterator()->current();
        $this->assertInstanceOf(
            ShipmentParameter::class,
            $parameter
        );
    }

    /**
     * Test an order that is fully backordered
     *
     * @return void
     * @throws \App\Exceptions\RecordExistException
     */
    public function testBackorderItems()
    {
        $this->resetSampleData();
        $this->sampleItemData['backorder_reason_code'] = '4';
        $this->sampleItemData['backorder_quantity'] = '10';
        $this->sampleItemData['expected_delivery_quantity'] = '0';
        $this->sampleItemData['order_quantity'] = '10';
        $this->shipmentTracker->reset();
        $this->shipmentTracker->addOrder($this->sampleOrderData);
        $this->shipmentTracker->addItem($this->sampleItemData);
        $this->assertEquals(1, $this->shipmentTracker->count());

        $parameter = $this->shipmentTracker->getIterator()->current();
        $this->assertInstanceOf(
            ShipmentLineChangeParameter::class,
            $parameter
        );
    }


    /**
     * Test an order with an item has partially
     * shipped and the reset are in backordered
     *
     * @return void
     * @throws \App\Exceptions\RecordExistException
     */
    public function testAddMixItems()
    {
        $this->resetSampleData();
        $this->sampleItemData['backorder_reason_code'] = '4';
        $this->sampleItemData['backorder_quantity'] = '10';
        $this->sampleItemData['order_quantity'] = '10';
        $this->shipmentTracker->reset();
        $this->shipmentTracker->addOrder($this->sampleOrderData);
        $this->shipmentTracker->addItem($this->sampleItemData);
        $this->assertEquals(2, $this->shipmentTracker->count());

        $count = 2;
        foreach ($this->shipmentTracker as $item) {
            if ($item instanceof ShipmentLineChangeParameter) {
                $count--;
            }
            if ($item instanceof ShipmentParameter) {
                $count--;
            }
        }
        $this->assertEquals(0, $count);
    }

    public function testCustomCarrier()
    {
        $this->resetSampleData();
        $this->sampleItemData['carrier_name'] = 'FEDEX INTL FIRST';
        $this->shipmentTracker->reset();
        $this->shipmentTracker->addOrder($this->sampleOrderData);
        $this->shipmentTracker->addItem($this->sampleItemData);
        $shipment = Arr::first($this->shipmentTracker->getIterator());
        $package = Arr::first($shipment->packages);
        $this->assertEquals(Tracker::DEFAULT_CARRIER, $package->carrier);
    }

    public function testMatchedCarrier()
    {
        $this->resetSampleData();
        $this->sampleItemData['carrier_name'] = 'UPS 2ND DAY AIR A.M.';
        $this->shipmentTracker->reset();
        $this->shipmentTracker->addOrder($this->sampleOrderData);
        $this->shipmentTracker->addItem($this->sampleItemData);
        $shipment = Arr::first($this->shipmentTracker->getIterator());
        $package = Arr::first($shipment->packages);
        $this->assertEquals('UPS', $package->carrier);
    }


    private function resetSampleData()
    {
        $this->sampleOrderData = [
            'order_number' => 1,
            FlatOrder::ORDER_ID_FIELD => '1234'
        ];
        $this->sampleItemData = [
            'order_number' => 1,
            'expected_delivery_quantity' => '1',
            'backorder_reason_code' => '5',
            'nve' => '1',
            'carrier_name' => 'ups',
            'customer_order_line_ref' => '1',
            'backorder_quantity' => '0',
            'catalogue_item_barcode' => '123456'
        ];
        return $this;
    }
}
