<?php

namespace App\MES\Handler;

use App\Core\Handlers\DatabaseStockHandler;
use App\MES\Handler\IO\FlatStock;
use Illuminate\Support\Facades\Log;

/**
 * StockIO
 *
 * @category App\Models\Warehouse\Handler
 * @package  App\Models\Warehouse\Handler
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class StockHandler extends DatabaseStockHandler
{
    protected $name = 'mes_stock';

    /**
     * StockIO constructor.
     *
     * Further specifies FlatStock as a dependency
     *
     * @param FlatStock $ioAdapter
     * @param Log       $logger
     */
    public function __construct(FlatStock $ioAdapter, Log $logger)
    {
        parent::__construct($ioAdapter, $logger);
    }
}
