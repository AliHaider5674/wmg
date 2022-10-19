<?php
namespace App\Models\Service\Model\Stock;

use App\MES\Handler\IO\Stock\BatchInfo;
use App\Models\Service\Model\Serialize;

/**
 * Represents Stock update API payload
 *
 * https://omsdocs.magento.com/en/specifications/#magento.inventory.source_stock_management
 *
 * Class Stock
 * @category WMG
 * @package  App\Models\Service\Model
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Batch extends Serialize
{
    public $processId;
    public $processNumber;
    public $processTotal;

    public function __construct(BatchInfo $batchInfo)
    {
        $this->processId     = $batchInfo->getProcessId();
        $this->processNumber = $batchInfo->getProcessNumber();
        $this->processTotal  = $batchInfo->getProcessTotal();
    }
}
