<?php declare(strict_types=1);

namespace App\Core\Repositories;

use WMGCore\Repositories\BaseRepository;
use App\Models\Order;

/**
 * Class OrderRepository
 * @package App\Core
 */
class OrderRepository extends BaseRepository
{
    public function __construct(
        Order $order
    ) {
        parent::__construct($order);
    }

    public function getOrderByRequest($salesChannel, $requestId)
    {
        return $this->modelQuery()->where('sales_channel', '=', $salesChannel)
            ->where('request_id', '=', $requestId)
            ->first();
    }
}
