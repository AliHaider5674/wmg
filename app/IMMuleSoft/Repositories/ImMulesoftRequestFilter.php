<?php

namespace App\IMMuleSoft\Repositories;

use App\IMMuleSoft\Models\ImMulesoftRequest;

/**
 * Class ImMulesoftRequestFilter
 * @package App\IMMuleSoft\Repositories
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ImMulesoftRequestFilter
{
    protected array $status = [ImMulesoftRequest::STATUS_RECEIVED];
    protected int $attempts = 3;
    protected string $resourceType = '';
    protected int $size = 100;

    /**
     * @return array
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * @param array $status
     * @return ImMulesoftRequestFilter
     */
    public function setStatus(array $status): ImMulesoftRequestFilter
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     * @return ImMulesoftRequestFilter
     */
    public function setAttempts(int $attempts): ImMulesoftRequestFilter
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * @param string $resourceType
     * @return ImMulesoftRequestFilter
     */
    public function setResourceType(string $resourceType): ImMulesoftRequestFilter
    {
        $this->resourceType = $resourceType;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return ImMulesoftRequestFilter
     */
    public function setSize(int $size): ImMulesoftRequestFilter
    {
        $this->size = $size;
        return $this;
    }
}
