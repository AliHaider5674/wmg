<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Models\ShipmentItem;
use WMGCore\Repositories\BaseRepository;
use Generator;
use Illuminate\Support\Facades\Crypt;

/**
 * Class ShipmentItemRepository
 * @package App\Core\Repository
 * @SuppressWarnings(PHPMD)
 */
class ShipmentItemRepository extends BaseRepository
{
    public function __construct(
        ShipmentItem $shipmentItem
    ) {
        parent::__construct($shipmentItem);
    }
}
