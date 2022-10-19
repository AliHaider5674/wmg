<?php declare(strict_types=1);

namespace App\Repositories;

use WMGCore\Repositories\BaseRepository;
use App\Models\OrderDrop as OrderDropModel;

/**
 * Class OrderDropRepository
 * @package App\Repositories
 */
class OrderDropRepository extends BaseRepository
{
    /**
     * OrderDropRepository constructor.
     * @param OrderDropModel $model
     */
    public function __construct(OrderDropModel $model)
    {
        parent::__construct($model);
    }
}
