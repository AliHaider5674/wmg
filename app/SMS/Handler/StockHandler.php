<?php

namespace App\SMS\Handler;

use App\Core\Handlers\DatabaseStockHandler;
use App\SMS\Handler\IO\Stock as SMSStock;
use Illuminate\Support\Facades\Log;

/**
 * SMS Stock Handler
 *
 * @category App\Models\Warehouse\Handler
 * @package  App\Models\Warehouse\Handler
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class StockHandler extends DatabaseStockHandler
{
    /**
     * @var string
     */
    protected $name = 'sms_stock';

    /**
     * StockIO constructor.
     *
     * Further specifies FlatStock as a dependency
     *
     * @param SMSStock $ioAdapter
     * @param Log       $logger
     */
    public function __construct(SMSStock $ioAdapter, Log $logger)
    {
        parent::__construct($ioAdapter, $logger);
    }
}
