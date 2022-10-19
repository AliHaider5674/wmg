<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Exception;

/**
 * File system failed
 * When doing file operation include remote and local
 *
 * Class FileSystemFailed
 * @category WMG
 * @package  App\Events
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FileSystemFailed
{
    use SerializesModels;

    public $connectionName;
    public $exception;

    /**
     * FileSystemConnectionFailed constructor.
     *
     * @param string     $connectionName
     * @param \Exception $exception
     */
    public function __construct(String $connectionName, Exception $exception)
    {
        $this->connectionName = $connectionName;
        $this->exception = $exception;
    }
}
