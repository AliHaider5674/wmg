<?php

namespace App\MES\Handler\IO;

use App\Core\Handlers\IO\AbstractFileImport;

/**
 * Abstract class for all MES files import
 * 1. It will move the file to different location after finish
 * 2. rollback data if error occur.
 *
 * Class ImportAbstract
 * @category WMG
 * @package  App\MES\Handler\IO
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class ImportAbstract extends AbstractFileImport
{
    /**
     * File name prefix
     * @var string
     */
    protected $filenamePrefix = 'DESADV_WMGUSM';

    /**
     * @return string
     */
    protected function getFilePattern(): string
    {
        return $this->filenamePrefix . '*.txt';
    }
}
