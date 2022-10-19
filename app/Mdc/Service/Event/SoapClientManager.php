<?php
namespace App\Mdc\Service\Event;

use App\Models\Service;
use App\Mdc\Clients\SoapClient;

/**
 * A manager that manage soap client for different services
 *
 * Class SoapClientManager
 * @category WMG
 * @package  App\Mdc\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class SoapClientManager
{
    private $clients = [];
    private $isDisabledSsl;

    /**
     * @param string $soapClientClass
     * @param false  $isDisabledSsl
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct($isDisabledSsl = false)
    {
        $this->isDisabledSsl = $isDisabledSsl;
    }

    public function setClient($id, $client)
    {
        $this->clients[$id] = $client;
    }

    /**
     * Get Client
     * @param \App\Models\Service $service
     * @return \SoapClient
     */
    public function getClient(Service $service)
    {
        if (!isset($this->clients[$service->id])) {
            $setting = $service->getAddition();
            $options = [];
            if (isset($setting['auth_username'])) {
                $options['login'] = $setting['auth_username'];
            }
            if (isset($setting['auth_password'])) {
                $options['password'] = $setting['auth_password'];
            }
            $client = app()->make(SoapClient::class, [
                'wsdl' => $setting['wsdl'],
                'options' => $options
            ]);
            $client->config($service->getAddition());
            $this->clients[$service->id] = $client;
        }
        return $this->clients[$service->id];
    }
}
