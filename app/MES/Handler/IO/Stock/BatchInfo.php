<?php

namespace App\MES\Handler\IO\Stock;

/**
 * BatchInfo
 *
 * @category App\MES\Handler\IO\Stock
 * @package  App\MES\Handler\IO\Stock
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class BatchInfo
{
    public $processId;
    public $processNumber;
    public $processTotal;


    /**
     * getProcessId
     * @return mixed
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * getProcessNumber
     * @return mixed
     */
    public function getProcessNumber()
    {
        return $this->processNumber;
    }

    /**
     * getProcessTotal
     * @return mixed
     */
    public function getProcessTotal()
    {
        return $this->processTotal;
    }
}
