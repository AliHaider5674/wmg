<?php

namespace App\Models\Service\ModelBuilder;

use App\Exceptions\RecordExistException;
use App\Models\Service\ModelBuilder\Shipment\ItemParameter;
use App\Models\Service\ModelBuilder\Shipment\PackageParameter;
use Exception;

/**
 * Shipment builder parameter
 *
 * Class ShipmentParameter
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentParameter extends Parameter
{
    public $orderId;
    /** @var PackageParameter[] */
    public $packages = [];

    /**
     * Add package
     *
     * @param PackageParameter $packageParameter
     *
     * @return $this
     * @throws \App\Exceptions\RecordExistException
     */
    public function addPackage(PackageParameter $packageParameter)
    {
        if (isset($packageParameter->packageId)) {
            if (isset($this->packages[$packageParameter->packageId])) {
                throw new RecordExistException('Package already exist.');
            }
            $this->packages[$packageParameter->packageId] = $packageParameter;
            return $this;
        }
        $this->packages[] = $packageParameter;
        $packageParameter->packageId = $this->packageCount() - 1;
        return $this;
    }

    /**
     * Check if already have package
     * @param $packageId
     * @return bool
     */
    public function hasPackage($packageId)
    {
        return array_key_exists($packageId, $this->packages);
    }

    public function packageCount()
    {
        return count($this->packages);
    }

    /**
     * Add items to package
     * If package id is not set, add to the last package
     *
     * @param ItemParameter $item
     * @param $packageId
     *
     * @return $this
     * @throws \Exception
     */
    public function addItemToPackage(ItemParameter $item, $packageId = null)
    {
        if ($this->packageCount()<=0) {
            throw new Exception('No package available.');
        }

        if ($packageId === null) {
            $packageId = $this->packageCount() - 1;
        }

        if (!isset($this->packages[$packageId]->shippedQtyMap[$item->orderItemId])) {
            $this->packages[$packageId]->shippedQtyMap[$item->orderItemId] = 0;
        }
        $this->packages[$packageId]->addHiddenItem($item);
        $this->packages[$packageId]->itemIds[] = (int) $item->orderItemId;
        $this->packages[$packageId]->shippedQtyMap[$item->orderItemId] += $item->quantity;
        return $this;
    }
}
