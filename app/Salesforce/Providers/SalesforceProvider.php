<?php

namespace App\Salesforce\Providers;

use App\Core\Providers\FulfillmentAbstractProvider;
use App\Salesforce\ServiceClients\Handlers\AckHandler;
use App\Salesforce\ServiceClients\Handlers\ShipmentHandler;
use App\Salesforce\ServiceClients\Handlers\StockHandler;
use App\Salesforce\ServiceClients\OmsRestfulClient;
use App\Salesforce\ServiceClients\RestfulClient;

/**
 * Class SalesforceProvider
 * @package App\Salesforce\Providers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class SalesforceProvider extends FulfillmentAbstractProvider
{
    /**
     * Fulfillment service events handler
     */
    protected const SERVICE_CLIENTS = [
        [
            'client' => RestfulClient::class,
            'handlers' => [
                StockHandler::class
            ]
        ],
        [
            'client' => OmsRestfulClient::class,
            'handlers' => [
                AckHandler::class,
                ShipmentHandler::class
            ]
        ]
    ];

    /**
     * getNamespace
     * @return string
     */
    protected function getNamespace(): string
    {
        return 'salesforce';
    }
}
