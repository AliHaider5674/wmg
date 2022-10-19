<?php

namespace App\Core\Services\Converters;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Core\Models\RawData\Order as RawOrder;
use App\Core\Models\RawData\OrderItem as RawOrderItem;
use App\Core\Models\RawData\OrderAddress as RawOrderAddress;
use App\Models\OrderAddress as EloquentOrderAddress;
use App\Models\OrderItem as EloquentOrderItem;

/**
 * Convert order model to raw order data that is easier
 * for fulfillment IO to use
 *
 * Class OrderRawConverterService
 * @category WMG
 * @package  App\Core\Services\Converters
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class OrderRawConverterService
{
    /**
     * Get raw order from Order and a collection of Order Items
     *
     * @param Order      $order
     * @param Collection $itemCollection
     * @return RawOrder
     */
    public function getRawOrder(
        Order $order,
        Collection $itemCollection
    ): RawOrder {
        /** @todo Move these to a factory service*/
        $rawOrder = new RawOrder();

        $rawOrder->fill($order->toArray(), false);
        $rawOrder->customAttributes = $order->getCustomAttributes();

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $rawOrder->billingAddress = $this->getRawAddress($billingAddress);
        $rawOrder->shippingAddress = $this->getRawAddress($shippingAddress);

        $rawOrder->items = $itemCollection->map(function (EloquentOrderItem $item) {
            return $this->getRawOrderItem($item);
        })->toArray();

        $mainAddress = $shippingAddress ?? $billingAddress;

        if ($mainAddress) {
            $rawOrder->customerName = $mainAddress->getFullName();
        }

        return $rawOrder;
    }

    /**
     * Get a raw OrderAddress from an Eloquent OrderAddress
     *
     * @todo Move this to a factory service
     * @param EloquentOrderAddress|null $orderAddress
     * @return RawOrderAddress
     */
    private function getRawAddress(
        ?EloquentOrderAddress $orderAddress
    ): ?RawOrderAddress {
        if ($orderAddress === null) {
            return null;
        }

        $rawAddress = new RawOrderAddress();

        $rawAddress->fill($orderAddress->toArray(), false);
        $rawAddress->customerName = $orderAddress->getFullName();
        $rawAddress->customAttributes = $this->getAddressCustomAttributesArray(
            $orderAddress
        );

        return $rawAddress;
    }

    /**
     * Get a raw OrderItem from an Eloquent OrderItem
     *
     * @todo Change this to be in a factory service
     * @param EloquentOrderItem $orderItem
     * @return RawOrderItem
     */
    private function getRawOrderItem(EloquentOrderItem $orderItem): RawOrderItem
    {
        $rawOrderItem = new RawOrderItem();
        $rawOrderItem->fill($orderItem->toArray(), false);
        $rawOrderItem->customAttributes = $orderItem->getCustomAttributes();

        return $rawOrderItem;
    }

    /**
     * This will work for the way the custom attributes currently are as well as
     * with the changes in the Tax ID pull request
     *
     * @param Model $model
     * @return array|null
     */
    private function getAddressCustomAttributesArray(Model $model): ?array
    {
        if (empty($model->custom_attributes)) {
            return null;
        }

        if (is_array($model->custom_attributes)) {
            return $model->custom_attributes;
        }

        if (!is_string($model->custom_attributes)) {
            return null;
        }

        $customAttributesDecoded = json_decode(
            $model->custom_attributes,
            true
        );

        if (!$customAttributesDecoded) {
            return null;
        }

        return $customAttributesDecoded;
    }
}
