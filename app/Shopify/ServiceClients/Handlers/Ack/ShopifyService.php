<?php

namespace App\Shopify\ServiceClients\Handlers\Ack;

/**
 * Class ShopifyHandler
 * @package App\Shopify\ServiceClients\Handlers\Ack
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ShopifyService
{
    private const ORDER_FIELDS = 'note, tags';

    /**
     * getExistingOrderReasons
     * @param $client
     * @param $orderId
     * @return mixed
     */
    public function getExistingShopifyOrder($client, $orderId) : array
    {
        $params = ['fields'=> self::ORDER_FIELDS];
        return $client->Order($orderId)->get($params);
    }

    /**
     * updateOrder
     * @param $client
     * @param $orderId
     * @param array $postData
     * @return array
     */
    public function updateOrder($client, $orderId, array $postData) : array
    {
        //As we cannot update the order statues of Shopify orders.
        //send an on_hold order tag to any orders with applicable reason codes
        //send order messages detailing the reason and item to shopify order
        if (!empty($postData)) {
            return $client->Order($orderId)->put($postData);
        }
        return array();
    }
}
