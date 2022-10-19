<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Models\RawData\OrderItem;
use WMGCore\Repositories\BaseRepository;

/**
 * Class OrderRepository
 * @package App\Core
 */
class OrderItemRepository extends BaseRepository
{
    public function __construct(
        OrderItem $orderItem
    ) {
        parent::__construct($orderItem);
    }
}
