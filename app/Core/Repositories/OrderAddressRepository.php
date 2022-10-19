<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Models\RawData\OrderAddress;
use WMGCore\Repositories\BaseRepository;

/**
 * Class OrderRepository
 * @package App\Core
 */
class OrderAddressRepository extends BaseRepository
{
    public function __construct(
        OrderAddress $orderAddress
    ) {
        parent::__construct($orderAddress);
    }
}
