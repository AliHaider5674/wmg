<?php
namespace App\Mom\Models\Service\Event\ClientHandler;

use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Mom\Helpers\ReasonCodeHelper;
use App\Mom\Models\Service\Event\EventMap;
use App\Core\Services\EventService;
use MomApi\Client;
use WMGCore\Services\ConfigService;

/**
 * This handler forward the call directly to MOM with any
 * special logic
 *
 * Class DefaultHandler
 * @category WMG
 * @package  App\Mom\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class DefaultHandler extends HandlerAbstract
{
    protected $handEvents = [
        EventService::EVENT_ITEM_SHIPPED,
        EventService::EVENT_SOURCE_UPDATE,
    ];

    protected $eventMap;
    protected $configService;
    protected $reasonCodeHelper;
    public function __construct(
        EventMap $eventMap,
        ConfigService $configService,
        ReasonCodeHelper $reasonCodeHelper
    ) {
        $this->eventMap = $eventMap;
        $this->configService = $configService;
        $this->reasonCodeHelper = $reasonCodeHelper;
    }

    /**
     * Handle all requests
     * @param string $eventName
     * @param \App\Models\Service\Event\RequestData\RequestDataInterface $request
     * @param Client                                                     $client
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        $momEvent = $this->eventMap->getMomEvent($eventName);
        return $client->publish($momEvent, $request->getData()->toArray(false), '*');
    }
}
