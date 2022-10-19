<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Models\Shipment;
use App\Core\Models\Warehouse;
use WMGCore\Repositories\BaseRepository;
use App\Core\Repositories\ShipmentItemRepository;

/**
 * Class WarehouseRepository
 * @package App\Core\Repository
 * @SuppressWarnings(PHPMD)
 */
class WarehouseRepository extends BaseRepository
{
    public function __construct(
        Warehouse $warehouse
    ) {
        parent::__construct($warehouse);
    }

    public function getWarehousesByCodes(array $codes)
    {
        return $this->modelQuery()->whereIn('code', $codes)
            ->get();
    }
}
