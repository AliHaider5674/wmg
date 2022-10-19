<?php

namespace App\Shopify\ServiceClients\Handlers\Ack;

/**
 * Class Reason
 * @package App\Shopify\ServiceClients\Handlers\Ack
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class Ack
{
    public string $orderId;
    public array $data = array();
}
