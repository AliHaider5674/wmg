<?php

namespace App\Shopify\Services;

use App\Models\Service;
use App\Shopify\Clients\ShopifySDK;

/**
 * Class ClientService
 * @package App\Shopify\Services
 *
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class UrlService
{
    public function getQuery($parameters)
    {
        $queryArray = [];
        foreach ($parameters as $key => $parameter) {
            if (is_array($parameter)) {
                $queryArray[] = array_reduce($parameter, function ($result, $value) use ($key) {
                    return ($result ? $result . '&' : '') . $key . '[]='. urlencode($value);
                });
                continue;
            }
            $queryArray[] = $key . '=' . urlencode($parameter);
        }
        return implode('&', $queryArray);
    }
}
