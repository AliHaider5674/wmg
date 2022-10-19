<?php

namespace App\Models\Data;

/**
 * An abstract class that has basic rollback methods
 *
 * Class Rollbackable
 *
 * @category WMG
 * @package  App\Models\Data
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
abstract class Rollbackable
{
    /**
     * @var array
     */
    protected $processed = [];

    /**
     * @param       $item
     * @param mixed ...$args
     */
    abstract protected function rollbackItem($item, ...$args): void;

    /**
     * @param $object
     */
    protected function recordProcessed($object): void
    {
        $this->processed[] = $object;
    }

    /**
     * Pop a processed object
     *
     * @return mixed
     */
    protected function popProcessed()
    {
        return array_shift($this->processed);
    }

    /**
     * @param mixed ...$args
     * @return void
     */
    public function rollback(...$args): void
    {
        while ($object = $this->popProcessed()) {
            $this->rollbackItem($object, ...$args);
        }
    }

    public function removeAllRecordedProcessed()
    {
        $this->processed = [];
    }
}
