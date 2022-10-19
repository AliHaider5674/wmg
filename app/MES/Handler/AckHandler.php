<?php

namespace App\MES\Handler;

use App\MES\Handler\IO\FlatAck;
use App\Core\Services\EventService;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use Illuminate\Support\Facades\Log;
use App\Core\Handlers\AckHandler as BaseAckHandler;

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
class AckHandler extends BaseAckHandler
{
    /**
     * @var string
     */
    protected $name = 'ack';

    /**
     * AckHandler constructor.
     * @param FlatAck                   $ioAdapter
     * @param Log                       $log
     * @param EventService              $eventManager
     * @param ShipmentLineChangeBuilder $shipmentLineChangeBuilder
     */
    public function __construct(
        FlatAck $ioAdapter,
        EventService $eventManager,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $log
    ) {
        parent::__construct(
            $ioAdapter,
            $eventManager,
            $shipmentLineChangeBuilder,
            $log
        );
    }
}
