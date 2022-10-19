<?php

namespace App\Salesforce\ServiceClients\Handlers\Stock;

use Carbon\Carbon;
use JsonSerializable;

/**
 * Class BatchStockItem
 * @package App\Salesforce\ServiceClients\Handlers\Stock
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class BatchStockItem implements JsonSerializable
{
    private string $id;
    private string $externalRefId;
    private string $location;
    private float  $onHand;
    private string $sku = '';
    private string $effectiveDate;

    /**
     * @param string $sku
     */
    public function __construct(string $sku)
    {
        $this->sku = $sku;
        $this->setUniqueMessageIdentifier();

        $this->effectiveDate = Carbon::now()->toIso8601ZuluString();
    }

    /**
     * @param string $location
     * @return BatchStockItem
     */
    public function setLocation(string $location): BatchStockItem
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @param float $onHand
     * @return BatchStockItem
     */
    public function setOnHand(float $onHand): BatchStockItem
    {
        $this->onHand = $onHand;
        return $this;
    }

    /**
     * @param string $sku
     * @return BatchStockItem
     */
    public function setSku(string $sku): BatchStockItem
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @param string $effectiveDate
     * @return BatchStockItem
     */
    public function setEffectiveDate(string $effectiveDate): BatchStockItem
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    /**
     * setUniqueMessageIdentifier
     * @return $this
     */
    protected function setUniqueMessageIdentifier(): BatchStockItem
    {
        $id = md5($this->sku . time());

        $this->id = $id;
        $this->externalRefId = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getExternalRefId(): string
    {
        return $this->externalRefId;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return float
     */
    public function getOnHand(): float
    {
        return $this->onHand;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getEffectiveDate(): string
    {
        return $this->effectiveDate;
    }


    /**
     * jsonSerialize
     * @return string[]
     */
    public function jsonSerialize(): array
    {
        return
            [
                'externalRefId'  => $this->getExternalRefId(),
                'location' => $this->getLocation(),
                'onHand' => $this->getOnHand(),
                'sku' => $this->getSku(),
                'id'  => $this->getId(),
                'effectiveDate' => $this->getEffectiveDate()
            ];
    }
}
