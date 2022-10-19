<?php declare(strict_types=1);

namespace App\Core\Repositories;

use WMGCore\Repositories\BaseRepository;
use App\Models\OrderLog;

/**
 * Class OrderRepository
 * @package App\Core
 */
class OrderLogRepository extends BaseRepository
{
    public function __construct(
        OrderLog $orderLog
    ) {
        parent::__construct($orderLog);
    }

    public function addLog($orderId, $message, $status)
    {
        $this->create([
            'parent_id' => $orderId,
            'message' => $message,
            'status' => $status
        ]);
    }
}
