<?php declare(strict_types=1);

namespace App\Shopify\Constants;

/**
 * Class ConfigConstant
 * @package App\Printful\Constants
 */
class ConfigConstant
{
    const SUPPORTED_WAREHOUSES = 'shopify.supported.warehouses';
    const SHOPIFY_FULFILLMENT_REQUEST_SIZE = 'shopify.fulfillment_request.size';
    const ORDER_FETCH_CRON = 'fulfillment.shopify.fetch_orders.cron';
    const ORDER_EXPAND_CRON = 'fulfillment.shopify.expand_orders.cron';
    const FULFILLMENT_REQUEST_CRON = 'fulfillment.shopify.fulfillment_requests.cron';
    const ORDER_SCAN_CRON = 'fulfillment.shopify.order_scans.cron';
    const ORDER_FETCH_FAILED_FULFILLMENT_ORDERS_CRON = 'fulfillment.shopify.fetch_failed_fulfillment_orders.cron';
}
