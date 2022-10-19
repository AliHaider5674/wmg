<?php

namespace App\IMMuleSoft\Constants;

/**
 * Class ResourceConstant
 * @package App\IMMuleSoft\Constants
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ResourceConstant
{
    const STATUS_CODE_NO_DATA = 1;
    const RESOURCE_TYPE_STOCK_LEVEL = 'StockLevel';
    const RESOURCE_TYPE_PRODUCT_DATA_UPDATE = 'ProductDataUpdate';
    const RESOURCE_TYPE_SALES_ORDER_STATUS = 'SalesOrderStatus';
    const RESOURCE_TYPE_ASYNC_RESPONSE = 'Async';

    const RESPONSE_STATUS_CODE_SUCCESS = '0';
    const RESPONSE_MESSAGE_RECEIVED = 'received';
    const RESPONSE_MESSAGE_ACCEPTED = 'accepted';
    const RESPONSE_MESSAGE_SUCCESS = 'success';
}
