<?php

namespace App\IMMuleSoft\Http\Controllers;

use App\IMMuleSoft\Constants\ResourceConstant;

/**
 * Class ProductController
 * @package App\IMMuleSoft\Http\Controllers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ProductController extends AbstractController
{
    protected string $controllerType = 'product';
    protected string $resourceType = ResourceConstant::RESOURCE_TYPE_PRODUCT_DATA_UPDATE;
}
