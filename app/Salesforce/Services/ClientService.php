<?php

namespace App\Salesforce\Services;

use App\Models\Service;
use App\Salesforce\Clients\SalesforceSDK;
use Exception;

/**
 * Class ClientService
 * @package App\Salesforce\Services
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ClientService
{
    private SalesforceSDK $salesforceSDK;

    /**
     * @param SalesforceSDK $salesforceSDK
     */
    public function __construct(SalesforceSDK $salesforceSDK)
    {
        $this->salesforceSDK = $salesforceSDK;
    }

    /**
     * getClient
     * @param Service $service
     * @return SalesforceSDK
     * @throws Exception
     */
    public function getClient(Service $service) : SalesforceSDK
    {
        /**
         * additions
         *
         * required
         * - username
         * - password
         * - organizationId
         *
         * may be added in the future
         * - auth_url
         * - grant_type
         * - scope
         * - api_base_url
         */

        $addition = $service->getAddition();
        $this->salesforceSDK->setConfig($addition);
        return $this->salesforceSDK;
    }
}
