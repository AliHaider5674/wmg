<?php

namespace App\MES\Handler\IO;

use App\Services\FileSystemService;
use FileDataConverter\File\Flat;
use App\MES\Handler\IO\Ack\Tracker;
use App\MES\MesShipment;

/**
 * Handle mes flat ack file import
 *
 * Class FlatShipment
 * @category WMG
 * @package  App\MES\Handler\IO
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FlatAck extends ImportAbstract
{
    public const FILE_PREFIX = 'OTRCK_WMGUS';

    /**
     * @var string
     */
    protected $filenamePrefix = self::FILE_PREFIX;

    /**
     * @var array
     */
    protected $processedFiles;

    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * FlatAck constructor.
     * @param Flat              $fileIO
     * @param array             $config
     * @param FileSystemService $fileSystemService
     * @param Tracker           $tracker
     */
    public function __construct(
        Flat $fileIO,
        array $config,
        FileSystemService $fileSystemService,
        Tracker $tracker
    ) {
        parent::__construct($fileIO, $config, $fileSystemService);
        $this->tracker = $tracker;
    }

    /**
     * Start reading ack file
     * @param string $file
     * @param null|callable $callback
     * @return mixed|void
     */
    protected function startReadFile(string $file, callable $callback = null): void
    {
        $this->tracker->reset();
    }

    /**
     * Read line by line
     * @param string $file
     * @param array  $data
     * @param string $section
     * @param null   $callback
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed|void
     */
    protected function readFileLine(
        string $file,
        array $data,
        string $section,
        callable $callback = null
    ) {
        if ($section === 'line_sent') {
            $this->tracker->addItem($data);
        } elseif ($section === 'order_sent') {
            $this->tracker->addOrder($data);
        }
    }

    /**
     * Finish reading a file
     *
     * @param string $file
     * @param null|callable $callback
     *
     * @return mixed|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function finishReadFile(string $file, callable $callback = null)
    {
        foreach ($this->tracker as $lineModel) {
            $callback($lineModel);
        }
    }

    /**
     * Rollback
     *
     * @param $item
     * @return void
     */
    protected function rollbackItem($item, ...$args): void
    {
        if ($item instanceof MesShipment) {
            $item->status = MesShipment::STATUS_ERROR;
            $item->save();
        }
    }
}
