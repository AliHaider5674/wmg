<?php

namespace App\Core\Models\RawData;

use App\Models\Service\Model\Serialize;

/**
 * Raw order model for Fulfillment IO to use
 *
 * Class Order
 * @category WMG
 * @package  App\Core\Models\RawData
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD)
 */
class Order extends Serialize
{
    public $id;
    public $status;
    public $salesChannel;
    public $requestId;
    public $orderId;
    public $giftMessage;
    public $dropId;
    public $shippingMethod;
    public $customerId;
    public $customerReference;
    public $vatCountry;
    public $customAttributes = [];
    public $shippingGrossAmount;
    public $shippingNetAmount;
    public $shippingTaxAmount;
    public $createdAt;
    public $updatedAt;
    /** @var OrderItem[] */
    public $items = [];
    /** @var OrderAddress */
    public $shippingAddress;
    /** @var OrderAddress */
    public $billingAddress;
    public $customerName;
    public $storeName;
}
