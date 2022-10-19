<?php

namespace App\IMMuleSoft\Repositories;

use App\IMMuleSoft\Models\ImMulesoftRequest;
use WMGCore\Repositories\BaseRepository;

/**
 * Class ImMulesoftRequestRepository
 * @package App\IMMuleSoft\Repositories
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ImMulesoftRequestRepository extends BaseRepository
{

    /**
     * @param ImMulesoftRequest $model
     */
    public function __construct(
        ImMulesoftRequest $model
    ) {
        parent::__construct($model);
    }

    /**
     * getRequestsByFilter
     * @param ImMulesoftRequestFilter $filter
     * @return mixed
     */
    public function getRequestsByFilter(ImMulesoftRequestFilter $filter)
    {
        return $this->modelQuery()
            ->hasStatusIn($filter->getStatus())
            ->attempts($filter->getAttempts())
            ->resourceType($filter->getResourceType())
            ->limit($filter->getSize())
            ->get();
    }

    /**
     * isUnique
     * @param string $messageId
     * @param string $resourceType
     * @return int
     */
    public function isUnique(
        string $messageId,
        string $resourceType
    ): int {
        return !(bool) $this->modelQuery()
            ->where('message_id', $messageId)
            ->where('resource_type', $resourceType)
            ->count('id');
    }
}
