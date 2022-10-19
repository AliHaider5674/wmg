<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Enums\ServiceStatus;
use App\Core\Models\ServiceData;
use App\Models\Service;
use WMGCore\Repositories\BaseRepository;
use Generator;

/**
 * Class ServiceRepository
 * @package App\Core\Repository
 * @SuppressWarnings(PHPMD)
 */
class ServiceDataRepository extends BaseRepository
{
    public function __construct(
        ServiceData $serviceData
    ) {
        parent::__construct($serviceData);
    }

    public function getServiceData(Service $service, string $key)
    {
        return $this->modelQuery()->where('parent_id', $service->id)
            ->where('key', $key)
            ->first();
    }

    /**
     * @param \App\Models\Service $service
     * @param string              $key
     * @param                     $value
     * @return \App\Core\Models\ServiceData
     */
    public function updateServiceData(Service $service, string $key, $value)
    {
        $data = $this->getServiceData($service, $key);
        $value = is_array($value) ? json_encode($value) : $value;
        if ($data) {
            $this->update($data, ['value' => $value]);
            return $data;
        }
        return $this->create([
            'parent_id' => $service->id,
            'key' => $key,
            'value' => $value
        ]);
    }
}
