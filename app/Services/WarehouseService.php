<?php

namespace App\Services;

use App\Core\Handlers\AbstractHandler;
use App\Core\Handlers\FulfillmentHandlerContainer;
use App\Core\Handlers\HandlerInterface;
use Exception;

/**
 * A warehouse services that use different handlers
 * to do order drop, shipment import, stock import and ack
 *
 * Class WarehouseService
 * @category WMG
 * @package  App\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class WarehouseService
{
    /**
     * @var FulfillmentHandlerContainer
     */
    private $fulfillmentHandlerContainer;

    /**
     * WarehouseService constructor.
     * @param FulfillmentHandlerContainer $fulfillmentHandlerContainer
     */
    public function __construct(FulfillmentHandlerContainer $fulfillmentHandlerContainer)
    {
        $this->fulfillmentHandlerContainer = $fulfillmentHandlerContainer;
    }

    /**
     * Get handler types
     *
     * @return array
     */
    public function getFulfillmentHandlerTypes(): array
    {
        return $this->fulfillmentHandlerContainer->getHandlerTypes();
    }

    /**
     * @param string $type
     * @return iterable
     */
    public function getHandlers(string $type): iterable
    {
        return $this->fulfillmentHandlerContainer->getHandlers($type);
    }

    /**
     * Check if a handler can handle the data
     *
     * @param AbstractHandler $handler
     * @return void
     * @throws Exception
     */
    public function callHandler(HandlerInterface $handler): void
    {
        try {
            $isAllowed = $handler->validate();

            if ($isAllowed) {
                $handler->handle();
            }
        } catch (Exception $e) {
            if (method_exists($handler, 'rollback')) {
                $handler->rollback($e->getMessage());
            }

            throw $e;
        }
    }
}
