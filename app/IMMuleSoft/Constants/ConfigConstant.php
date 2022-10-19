<?php

namespace App\IMMuleSoft\Constants;

/**
 * Class ConfigConstant
 * @package App\IMMuleSoft\Constants
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ConfigConstant
{
    const IMMULESOFT_SOURCE_MAP = 'immulesoft.source_id.map';
    const IMMULESOFT_SOURCE_ID = 'IM';
    const IMMULESOFT_ORDER_BATCH_SIZE = 'immulesoft.order.batch.size';

    const ORDER_CRON = 'fulfillment.immulesoft.order.cron';
    const ORDER_STATUS_CRON = 'fulfillment.immulesoft.order.status.cron';
    const PRODUCT_CRON = 'fulfillment.immulesoft.product.cron';
    const STOCK_CRON = 'fulfillment.immulesoft.stock.cron';

    const BACKORDER_REASON_CODE_NO_STOCK = 4;
}
