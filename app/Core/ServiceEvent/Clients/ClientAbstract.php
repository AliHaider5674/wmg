<?php
namespace App\Core\ServiceEvent\Clients;

use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use App\Models\Service\Event\RequestData\StandardRequest;
use App\Models\ServiceEventCall;

/**
 * Client abstract which has handles to do the requests
 * A request can be handled with multiple handlers.
 *
 * Class ClientAbstract
 * @category WMG
 * @package  App\Models\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
abstract class ClientAbstract implements ClientInterface
{
    /**
     * @var iterable
     */
    protected $handlers;

    protected NetworkClientService $networkClientService;

    private string $networkClientName;
    /**
     * ClientAbstract constructor.
     * @param iterable $handlers
     */
    public function __construct(
        iterable $handlers,
        string $networkClientName,
        NetworkClientService $networkClientService
    ) {
        $this->handlers = $handlers;
        $this->networkClientName = $networkClientName;
        $this->networkClientService = $networkClientService;
    }

    /**
     * @param ServiceEventCall $eventCall
     * @return mixed
     */
    protected function getClient(ServiceEventCall $eventCall)
    {
        /** @var \App\Models\Service $service */
        $service = $eventCall->serviceEvent->service;
        return $this->networkClientService->getClient($service, $this->networkClientName);
    }


    /**
     * Add an handler for the requests
     * @param HandlerInterface $handler
     * @return $this
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * Make request for a give event call
     * @param ServiceEventCall $eventCall
     * @return array
     * @throws \Exception
     */
    public function request(ServiceEventCall $eventCall): array
    {
        $eventName = $eventCall->serviceEvent->event;

        $client = $this->getClient($eventCall);

        $result = [];
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($eventName)) {
                try {
                    $result[get_class($handler)] = $this->handle($handler, $eventName, $eventCall, $client);
                } catch (\Exception $e) {
                    $result[get_class($handler)] = $e->getMessage();
                    throw $e;
                }
            }
        }
        return $result;
    }

    /**
     * Handle request
     *
     * @param HandlerInterface             $handler
     * @param string                       $eventName
     * @param ServiceEventCall             $eventCall
     * @param                              $client
     *
     * @return |null
     */
    protected function handle(HandlerInterface $handler, String $eventName, ServiceEventCall $eventCall, $client)
    {
        $request = new StandardRequest($eventCall->getData());

        return $handler->handle(
            $eventName,
            $request,
            $client
        );
    }
}
