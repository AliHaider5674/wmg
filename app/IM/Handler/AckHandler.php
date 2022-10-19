<?php

namespace App\IM\Handler;

use App\Core\Handlers\AckHandler as BaseAckHandler;
use App\Core\Services\EventService;
use App\IM\Configurations\ImConfig;
use App\IM\Handler\IO\ApiAck;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use Illuminate\Support\Facades\Log;

/**
 * Class AckHandler
 * @package App\IM\Handler
 */
class AckHandler extends BaseAckHandler
{
    /**
     * @var string
     */
    public $name = 'im.ack';

    /**
     * AckHandler constructor.
     * @param ApiAck                    $ioAdapter
     * @param EventService              $eventManager
     * @param ShipmentLineChangeBuilder $shipmentLineChangeBuilder
     * @param Log                       $logger
     * @param ImConfig                  $config
     */
    public function __construct(
        ApiAck $ioAdapter,
        EventService $eventManager,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $logger,
        ImConfig $config
    ) {
        parent::__construct(
            $ioAdapter,
            $eventManager,
            $shipmentLineChangeBuilder,
            $logger,
            ['source_ids' => $config->getSourceIds()]
        );
    }
}
