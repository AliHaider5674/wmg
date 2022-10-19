<?php

namespace App\MES\Handler;

use App\Core\Handlers\AbstractShipmentHandler;
use App\Core\Repositories\ShipmentRepository;
use App\MES\Handler\IO\FlatShipment;
use App\Core\Services\EventService;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use Illuminate\Support\Facades\Log;
use App\Core\Handlers\Traits\AckHandler;

/**
 * Handle shipments
 *
 * Class ShipmentHandler
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentHandler extends AbstractShipmentHandler
{
    use AckHandler;
    protected $name = 'shipment';
    protected ShipmentLineChangeBuilder $shipmentLineChangeBuilder;
    public function __construct(
        FlatShipment $ioAdapter,
        EventService $eventManager,
        ShipmentRepository $shipmentRepository,
        ShipmentBuilder $shipmentBuilder,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $logger
    ) {
        $this->shipmentLineChangeBuilder = $shipmentLineChangeBuilder;
        parent::__construct($ioAdapter, $eventManager, $shipmentRepository, $shipmentBuilder, $logger);
    }


    /**
     * Process all shipments
     *
     * @return void
     */
    public function handle(): void
    {
        $this->removeAllRecordedProcessed();
        $this->ioAdapter->start();
        $this->ioAdapter->receive(function ($parameter) {
            try {
                if ($parameter instanceof  ShipmentLineChangeParameter) {
                    $this->processAckParameter($parameter);
                    return;
                }
                $this->processShipmentParameter($parameter);
            } catch (\Exception $e) {
                $failedParameter = $this->recordFailedParameter($parameter, $e->getMessage());
                $this->recordProcessed($failedParameter);
                $this->logger::debug('Processed shipment failed for' .
                                    $parameter->orderId . ':' .
                                    $e->getMessage());
                return;
            }
        });
        $this->ioAdapter->finish();
    }

    public function validate()
    {
        return true;
    }
}
