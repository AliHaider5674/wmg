<?php
namespace App\Models\Service\Event\ClientHandler;

/**
 * Request Handler abstract
 *
 * Class HandlerAbstract
 * @category WMG
 * @package  App\Models\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
abstract class HandlerAbstract implements HandlerInterface
{
    protected $handEvents = [];

    public function canHandle(string $eventName)
    {
        if (in_array('*', $this->handEvents)) {
            return true;
        }
        return in_array($eventName, $this->handEvents);
    }
}
