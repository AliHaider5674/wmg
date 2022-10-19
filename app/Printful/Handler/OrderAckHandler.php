<?php declare(strict_types=1);

namespace App\Printful\Handler;

use App\Core\Handlers\AckHandler;
use App\Core\Handlers\IO\NullStream;
use App\Core\Services\EventService;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use Illuminate\Support\Facades\Log;

/**
 * Class PrintfulAckService
 *
 * @package App\Printful\Service
 */
class OrderAckHandler extends AckHandler
{
    /**
     * PrintfulAckService constructor.
     * @param NullStream                $ioAdapter
     * @param EventService              $eventManager
     * @param ShipmentLineChangeBuilder $shipmentLineChangeBuilder
     * @param Log                       $logger
     */
    public function __construct(
        NullStream $ioAdapter,
        EventService $eventManager,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $logger
    ) {
        parent::__construct(
            $ioAdapter,
            $eventManager,
            $shipmentLineChangeBuilder,
            $logger
        );
    }
}
