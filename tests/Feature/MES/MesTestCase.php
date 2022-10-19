<?php
namespace Tests\Feature\MES;

use App\MES\Faker\AckFaker;
use App\MES\Faker\ShipmentFaker;
use Tests\Feature\WarehouseTestCase;
use App\Services\WarehouseService;

/**
 * Shipment shipment files
 *
 * Class ShipmentImportTest
 * @category WMG
 * @package  Tests\Feature\MES
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MesTestCase extends WarehouseTestCase
{
    /** @var WarehouseService */
    protected $warehouseService;
    /** @var ShipmentFaker */
    protected $shipmentFaker;
    /** @var AckFaker */
    protected $ackFaker;

    public function setUp():void
    {
        parent::setUp();
        $this->warehouseService = app()->make(WarehouseService::class);
        //SHIPMENT FAKER
        $this->shipmentFaker = app()->make(ShipmentFaker::class);
        //SHIPMENT FAKER
        $this->ackFaker = app()->make(AckFaker::class);
    }
}
