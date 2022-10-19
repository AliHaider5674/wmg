<?php

namespace App\IMMuleSoft\Models\Weight;

/**
 * Class Weight
 * @package App\IMMuleSoft\Models\Weight
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Weight
{
    const ZERO_WEIGHT = 0.000;
    /**
     * @var float
     */
    protected float $totalWeight = self::ZERO_WEIGHT;

    /**
     * @var string
     */
    protected string $weightUnit = 'g';

    protected string $message = '';

    /**
     * @return float
     */
    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }

    /**
     * @return float
     */
    public function getTotalWeightInKg(): float
    {
        return $this->totalWeight / 1000;
    }

    /**
     * incrementWeight
     * @param float $weight
     */
    public function incrementWeight(float $weight)
    {
        $this->totalWeight = $this->totalWeight + $weight;
    }

    /**
     * @return string
     */
    public function getWeightUnit(): string
    {
        return $this->weightUnit;
    }

    /**
     * @param string $weightUnit
     * @return Weight
     */
    public function setWeightUnit(string $weightUnit): Weight
    {
        $this->weightUnit = $weightUnit;
        return $this;
    }

    /**
     * setMessage
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): Weight
    {
        $this->message = $message;
        return $this;
    }

    /**
     * getMessage
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }
}
