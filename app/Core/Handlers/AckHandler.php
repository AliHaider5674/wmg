<?php

namespace App\Core\Handlers;

use App\Core\Handlers\Traits\ItemRollback;
use App\Models\FailedParameter;
use App\Core\Services\EventService;
use App\Models\Service\Model\ShipmentLineChange\Item as LineChangeItem;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use App\Models\OrderItem;
use App\Core\Handlers\IO\IOInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Core\Handlers\Traits\AckHandler as AckHandlerTraits;

/**
 * Handle ack files
 *
 * Class DigitalHandler
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AckHandler extends AbstractHandler
{
    use ItemRollback;
    use AckHandlerTraits;
    /**
     * @var EventService
     */
    protected $eventManager;

    /**
     * @var ShipmentLineChangeBuilder
     */
    protected $shipmentLineChangeBuilder;

    /**
     * AckHandler constructor.
     *
     * @param IOInterface           $ioAdapter
     * @param EventService              $eventManager
     * @param ShipmentLineChangeBuilder $shipmentLineChangeBuilder
     * @param Log                       $logger
     */
    public function __construct(
        IOInterface $ioAdapter,
        EventService $eventManager,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $logger
    ) {
        parent::__construct($ioAdapter, $logger);

        $this->eventManager = $eventManager;
        $this->shipmentLineChangeBuilder = $shipmentLineChangeBuilder;
    }

    /**
     * Handle digital item shipment
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->removeAllRecordedProcessed();
        $this->ioAdapter->start();
        $this->ioAdapter->receive([$this, 'processAckParameter']);
        $this->ioAdapter->finish();
    }

    public function validate()
    {
        return true;
    }
}
