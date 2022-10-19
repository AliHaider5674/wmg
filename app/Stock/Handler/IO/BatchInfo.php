<?php

namespace App\Stock\Handler\IO;

/**
 * Class BatchInfo
 *
 * Batch info for stock updates
 * @category WMG
 * @package  App\Stock\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
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
