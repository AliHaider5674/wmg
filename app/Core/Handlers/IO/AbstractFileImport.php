<?php

namespace App\Core\Handlers\IO;

use App\Models\Data\Rollbackable;
use App\Services\FileSystemService;
use FileDataConverter\File\FileInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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
abstract class AbstractFileImport extends Rollbackable implements IOInterface
{
    protected $processedFiles;
    protected $readyFiles;
    protected $fileIO;
    protected $fileSystemService;
    protected $remoteConnection;
    protected $localConnection;
    protected $tmpDir;
    protected $historyDir;
    protected $liveDir;
    protected $remoteFiles;

    /**
     * Start reading a file
     *
     * @param string $file
     * @param null $callback
     *
     * @return mixed
     */
    abstract protected function startReadFile(string $file, callable $callback = null);

    /**
     * Reading a line of a file
     *
     * @param string $file
     * @param array  $data
     * @param string $section
     * @param null   $callback
     *
     * @return mixed
     */
    abstract protected function readFileLine(string $file, array $data, string $section, callable $callback = null);

    /**
     * Finish reading a file
     *
     * @param string $file
     * @param null $callback
     *
     * @return mixed
     */
    abstract protected function finishReadFile(string $file, callable $callback = null);

    /**
     * @return string
     */
    abstract protected function getFilePattern(): string;

    /**
     * AbstractFileImport constructor.
     * @param FileInterface     $fileIO
     * @param array             $config
     * @param FileSystemService $fileSystemService
     */
    public function __construct(
        FileInterface $fileIO,
        array $config,
        FileSystemService $fileSystemService
    ) {
        $this->fileIO = $fileIO;
        $this->historyDir = $config['history_dir'];
        $this->liveDir = $config['live_dir'];
        $this->remoteConnection = $config['remote_connection'];
        $this->localConnection = $config['local_connection'];
        $this->tmpDir = $config['tmp_dir'];
        $this->fileSystemService = $fileSystemService;
    }

    /**
     * Start
     *
     * @param array|null $data
     * @return void
     * @throws FileNotFoundException
     */
    public function start(array $data = null): void
    {
        $filePath = $this->liveDir . '/'. $this->getFilePattern();
        $this->remoteFiles = $this->fileSystemService->useConnection($this->remoteConnection)
            ->getFiles($filePath);

        //Copy files to local
        $this->emptyTmpDir();
        $this->fileSystemService->useConnection($this->localConnection)
            ->copyFile($this->remoteFiles, $this->tmpDir, $this->remoteConnection);

        $this->readyFiles = $this->fileSystemService->useConnection($this->localConnection)
            ->getFiles($filePath);

        $this->processedFiles = [];
    }

    /**
     * Not using for import interface
     * @param $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     */
    public function send($data, $callback = null)
    {
        //This is not use for import
        return $this;
    }
    //@codingStandardsIgnoreEnd

    public function receive($callback)
    {
        foreach ($this->readyFiles as $file) {
            $this->startReadFile($file, $callback);
            $fileFullPath = $this->fileSystemService->useConnection($this->localConnection)
                ->getFullPath($file);
            $this->fileIO->read($fileFullPath, function ($data, $section) use ($fileFullPath, $callback) {
                $this->readFileLine($fileFullPath, $data, $section, $callback);
            });
            $this->finishReadFile($file, $callback);
            $this->processedFiles[] = $file;
        }
        return $this;
    }


    /**
     * Finish import
     * @param array|null $data
     * @return void
     * @throws FileNotFoundException
     */
    public function finish(array $data = null)
    {
        if (empty($this->historyDir)) {
            return;
        }

        //Move file
        if (!$this->fileSystemService->useConnection($this->remoteConnection)->exists($this->historyDir)) {
            $this->fileSystemService->useConnection($this->remoteConnection)->makeDir($this->historyDir);
        }

        $this->fileSystemService->useConnection($this->remoteConnection)
            ->move($this->remoteFiles, $this->historyDir);
    }


    protected function recordFile($fullPath)
    {
        $this->processedFiles[] = $fullPath;
        return $this;
    }

    /**
     * Empty tmp dir
     *
     * @return $this
     */
    protected function emptyTmpDir()
    {
        $this->fileSystemService->useConnection($this->localConnection)
            ->emptyDir($this->tmpDir);
        return $this;
    }
}
