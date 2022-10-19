<?php

namespace App\IMMuleSoft\Http\Controllers;

use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Constants\RouteConstant;

/**
 * Class AsyncResponseController
 * @package App\IMMuleSoft\Http\Controllers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class AsyncResponseController extends AbstractController
{
    protected string $controllerType = RouteConstant::ASYNC_RESPONSE_NAME;
    protected string $resourceType = ResourceConstant::RESOURCE_TYPE_ASYNC_RESPONSE;
}
