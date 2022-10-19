<?php

namespace App\SMS\Handler\IO;

use App\Core\Handlers\IO\AbstractFileImport;
use App\MES\MesStock;
use App\Models\Service\ModelBuilder\SourceParameter;
use App\Services\FileSystemService;
use App\SMS\Handler\IO\Stock\Tracker;
use App\SMS\SmsStock;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Log;
use FileDataConverter\File\Csv;

/**
 * Handle SMS flat Stock file import
 *
 *
 * @category WMG
 * @package  App\MES\Handler\IO
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Stock extends AbstractFileImport
{
    /**
     * File name prefix
     * @var string
     */
    protected $filePattern = 'SMS_Catalog_File_??????_??????.TXT';

    /**
     * @var Finder
     */
    protected $fileSystem;

    /**
     * @var Tracker
     */
    protected $sourceTracker;

    /**
     * FlatStock constructor.
     * @param Csv               $fileIO
     * @param array             $config
     * @param FileSystemService $fileSystemService
     * @param Tracker           $sourceTracker
     */
    public function __construct(
        Csv $fileIO,
        array $config,
        FileSystemService $fileSystemService,
        Tracker $sourceTracker
    ) {
        parent::__construct($fileIO, $config, $fileSystemService);
        $this->sourceTracker = $sourceTracker;

        if (!empty($config['file_pattern'])) {
            $this->filePattern = $config['file_pattern'];
        }
    }

    /**
     * @return string
     */
    protected function getFilePattern(): String
    {
        return $this->filePattern;
    }

    /**
     * Start reading stock file
     * @param string $file
     * @param null $callback
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed|void
     *
     * @codingStandardsIgnoreStart
     */
    protected function startReadFile(String $file, $callback = null)
    {
        $msg = sprintf("Start:%d \n", memory_get_usage());

        Log::debug($msg);

        $this->sourceTracker->reset();
    }

    /**
     * Read line by line
     * @param string $file
     * @param array  $data
     * @param string $section
     * @param null   $callback
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed|void
     * @codingStandardsIgnoreStart
     */
    protected function readFileLine(
        string $file,
        array $data,
        string $section,
        callable $callback = null
    ){
        if(!empty($data)) {
            $this->sourceTracker->addSourceStock($data);
        }
    }

    /**
     * Finish reading a file
     *
     * @param string        $file
     * @param callable|null $callback
     *
     * @return mixed|void
     */
    protected function finishReadFile(String $file, callable $callback = null)
    {
        $stock = new SmsStock();
        $stock->fill([
            'file' => pathinfo($file, PATHINFO_BASENAME),
            'status' => MesStock::STATUS_PROCESSING,
            'source_count' => $this->sourceTracker->getSourceStockCount(),
            'sku_count' => $this->sourceTracker->getTotalSkuCount()
        ]);
        $stock->save();
        $this->recordProcessed($stock);

        $msg = sprintf("Middle:%d \n", memory_get_usage());

        Log::debug($msg);

        $this->sourceTracker->buildSources();

        //append batch info to source if required.
        $this->sourceTracker->batchStockUpdates();

        $msg = sprintf("End:%d \n", memory_get_usage());

        Log::debug($msg);

        /**@var SourceParameter $sourceStockModel*/
        foreach ($this->sourceTracker->getIterator() as $sourceStockModel) {
            $callback($sourceStockModel);
        }
    }

    /**
     * Rollback item
     *
     * @param $item
     * @param array ...$args
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function rollbackItem($item, ...$args): void
    {
        if ($item instanceof SmsStock) {
            $item->status = SmsStock::STATUS_ERROR;
            $item->save();
        }
    }
}
