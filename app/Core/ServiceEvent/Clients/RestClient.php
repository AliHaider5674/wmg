<?php
namespace App\Core\ServiceEvent\Clients;

use App\Models\ServiceEventCall;

/**
 * A client that distribute event data to MOM
 *
 * Class MomClient
 * @category WMG
 * @package  App\Mom\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class RestClient implements ClientInterface
{
    protected $client;
    public function getName()
    {
        return 'rest';
    }

    /**
     * @param \App\Models\ServiceEventCall $serviceEventCall
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function request(ServiceEventCall $serviceEventCall)
    {
        return '';
    }
}
