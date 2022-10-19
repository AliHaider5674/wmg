<?php
namespace App\Core\Constants;

/**
 * Core configuration constants
 *
 * Class ConfigConstant
 * @category WMG
 * @package  App\Core\Constants
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class ConfigConstant
{
    const SHIPPING_METHOD_MAP = 'shipping_method.map';

    const FULFILLMENT_ACK_CRON = 'fulfillment.ack.cron';
    const FULFILLMENT_ORDER_CRON = 'fulfillment.order.cron';
    const FULFILLMENT_SHIPMENT_CRON = 'fulfillment.shipment.cron';
    const FULFILLMENT_DIGITAL_CRON = 'fulfillment.digital.cron';
}
