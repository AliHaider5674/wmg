<?php

namespace App\IMMuleSoft\Http\Controllers;

use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Constants\RouteConstant;

/**
 * Class OrderStatusController
 * @package App\IMMuleSoft\Http\Controllers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderStatusController extends AbstractController
{
    protected string $controllerType = RouteConstant::ORDER_STATUS_NAME;
    protected string $resourceType = ResourceConstant::RESOURCE_TYPE_SALES_ORDER_STATUS;
}
