<?php
namespace App\MES\Faker;

use App\Services\FileSystemService;
use Faker\Generator as DataFaker;
use FileDataConverter\File\Flat;

/**
 * Base faker class that generate fake MES files
 *
 * Class Faker
 * @category WMG
 * @package  App\MES\Faker
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
abstract class Faker
{
    protected $dataFaker;
    protected $fileIo;
    protected $exportDir;
    protected $filePrefix;
    protected $fileSystemService;
    protected $remoteConnection;

    abstract public function fake($data);

    public function __construct(
        Flat $fileIo,
        DataFaker $dataFaker,
        array $config,
        FileSystemService $fileSystemService
    ) {
        $this->dataFaker = $dataFaker;
        $this->fileIo = $fileIo;
        $this->exportDir = $config['dir'];
        $this->filePrefix = $config['prefix'];
        $this->remoteConnection = $config['remote_connection'];
        $this->fileSystemService = $fileSystemService;
    }


    /**
     * Output file to remote location
     * @param $data
     * @return string
     */
    protected function outputData($data)
    {
        $file = $this->exportDir . '/'
            . $this->filePrefix . '_'
            . date('YmdHis') . random_int(0, 1000) . '.txt';
        $fullFilePath = $this->fileSystemService
            ->useConnection($this->remoteConnection)
            ->getFullPath($file);
        $this->fileIo->write($fullFilePath, $data);
        return $file;
    }
}
