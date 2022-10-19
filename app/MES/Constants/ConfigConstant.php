<?php
namespace App\MES\Constants;

/**
 * Class ConfigConstant
 * @category WMG
 * @package  App\MES\Constants
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class ConfigConstant
{
    const MES_SOURCE_MAP = 'mes.source_id.map';
    const MES_ORDER_BATCH_SIZE = 'mes.order.batch.size';
    const MES_CHARSET = 'mes.charset';

    const STOCK_CRON = 'mes.stock.cron';
    const ACK_CRON = 'fulfillment.ack.cron';
    const ORDER_CRON = 'fulfillment.order.cron';
    const SHIPMENT_CRON = 'fulfillment.shipment.cron';
    const DIGITAL_CRON = 'fulfillment.digital.cron';
}
