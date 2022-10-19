<?php
namespace App\MES\Handler\IO;

use App\MES\MesStock;
use App\Services\FileSystemService;
use Symfony\Component\Finder\Finder;
use FileDataConverter\File\Flat;
use App\MES\Handler\IO\Stock\Tracker;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Facades\Log;

/**
 * Handle MES flat Stock file import
 *
 *
 * @category WMG
 * @package  App\MES\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FlatStock extends ImportAbstract
{
    /**
     * File import section type - row data
     */
    const SECTION_DATA_LINE = 'line';

    /**
     * File name prefix
     * @var string
     */
    protected $filenamePrefix = 'STOCK';

    /** @var Tracker */
    protected $sourceTracker;
    protected $processedFiles;

    /**
     * FlatStock constructor.
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
        $this->sourceTracker = $tracker;
    }

    /**
     * Start
     *
     * @param array|null $data
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function start(array $data = null): void
    {
        $filePath = $this->liveDir . '/'. $this->getFilePattern();
        $latestFile = $this->fileSystemService->useConnection($this->remoteConnection)
            ->getLatestFile($filePath);
        $this->remoteFiles = $this->fileSystemService->useConnection($this->remoteConnection)
            ->getFiles($filePath);
        //Copy files to local
        $this->emptyTmpDir();
        if ($latestFile) {
            $this->fileSystemService->useConnection($this->localConnection)
                ->copyFile([$latestFile], $this->tmpDir, $this->remoteConnection);
        }
        $this->readyFiles = $this->fileSystemService->useConnection($this->localConnection)
            ->getFiles($filePath);

        $this->processedFiles = [];
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
        //only concern with data lines
        if(self::SECTION_DATA_LINE == $section) {
            //save if db or tracker
            $this->sourceTracker->addSourceStock($data);
        }
    }

    /**
     * Finish reading a file
     *
     * @param string $file
     * @param null   $callback
     *
     * @return mixed|void
     */
    protected function finishReadFile(String $file, $callback = null)
    {
        $stock = new MesStock();
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

        foreach ($this->sourceTracker->getIterator() as $sourceStockModel) {
            /**@var \App\Models\Service\ModelBuilder\SourceParameter $sourceStockModel*/
            call_user_func($callback, $sourceStockModel);
        }
    }


    /**
     * rollbackItem
     * @param $item
     * @param array ...$args
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function rollbackItem($item, ...$args): void
    {
        if ($item instanceof MesStock) {
            $item->status = MesStock::STATUS_ERROR;
            $item->save();
        }
    }
}
