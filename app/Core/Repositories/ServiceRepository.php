<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use WMGCore\Repositories\BaseRepository;
use Generator;

/**
 * Class ServiceRepository
 * @package App\Core\Repository
 * @SuppressWarnings(PHPMD)
 */
class ServiceRepository extends BaseRepository
{
    public function __construct(
        Service $service
    ) {
        parent::__construct($service);
    }

    /**
     * Yield unprocessed PackageShipped events loading one at a time
     *
     * @return Generator
     */
    public function allService(): Generator
    {
        yield from $this->modelQuery()
            ->cursor();
    }

    public function getServiceByClient(Array $clients, $status = ServiceStatus::ACTIVE)
    {
        yield from $this->modelQuery()->where('status', $status)
            ->whereIn('client', $clients)->cursor();
    }

    /**
     * @param string $appId
     * @return \App\Models\Service|null
     */
    public function getByAppId(string $appId)
    {
        return $this->modelQuery()->where('app_id', $appId)->first();
    }

    /**
     * getById
     * @param $id
     * @param int $status
     * @return Builder|Model|object|null
     */
    public function getById($id, int $status = ServiceStatus::ACTIVE)
    {
        return $this->modelQuery()->where('status', $status)
            ->where('id', $id)->first();
    }
}
