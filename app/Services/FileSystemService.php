<?php
namespace App\Services;

use App\Events\FileSystemFailed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Sftp\SftpAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Cached\CachedAdapter;
use Exception;

/**
 * File System Wrapper
 *
 * Class FileSystem
 * @category WMG
 * @package  App\Models\FileSystem
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FileSystemService
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    private $currentConnection;
    private $currentConnectionName;
    /**
     * @var \Illuminate\Support\Facades\Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Set current connection
     * @param $connectionName
     * @return $this
     */
    public function useConnection($connectionName)
    {
        $this->currentConnection = $this->getConnection($connectionName);
        $this->currentConnectionName = $connectionName;
        return $this;
    }


    /**
     * Move files
     * @param $files
     *
     * @param      $dest
     * @param null $sourceConnectionName
     *
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function move($files, $dest, $sourceConnectionName = null)
    {
        try {
            if ($sourceConnectionName !== null && $this->currentConnectionName != $sourceConnectionName) {
                $sourceConnection = $this->getConnection($sourceConnectionName);
            }

            if (!is_array($files)) {
                if (!isset($sourceConnection)) {
                    $this->currentConnection->move($files, $dest);
                } else {
                    $this->currentConnection->put($dest, $sourceConnection->get($files));
                    $sourceConnection->delete($files);
                }

                return $this;
            }

            foreach ($files as $file) {
                $fileName = pathinfo($file, PATHINFO_BASENAME);
                if (!isset($sourceConnection)) {
                    $this->currentConnection->move($file, $dest . '/' . $fileName);
                } else {
                    $this->currentConnection->put($dest . '/'. $fileName, $sourceConnection->get($file));
                    $sourceConnection->delete($file);
                }
            }
        } catch (Exception $e) {
            $this->dispatchEventError($e);
        }
        return $this;
    }

    /**
     * Copy files
     *
     * @param String|array $files
     * @param string $dest
     * @param string $sourceConnectionName
     *
     * @return $this
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function copyFile($files, string $dest, string $sourceConnectionName = null)
    {
        try {
            if ($sourceConnectionName !== null && $this->currentConnectionName != $sourceConnectionName) {
                $sourceConnection = $this->getConnection($sourceConnectionName);
            }

            //Ensure dir exist
            $this->currentConnection->makeDirectory(pathinfo($dest, PATHINFO_DIRNAME));

            if (!is_array($files)) {
                if (!isset($sourceConnection)) {
                    $this->currentConnection->copy($files, $dest);
                } else {
                    $this->currentConnection->put($dest, $sourceConnection->get($files));
                }
                return $this;
            }
            foreach ($files as $file) {
                $fileName = pathinfo($file, PATHINFO_BASENAME);
                if (!isset($sourceConnection)) {
                    $this->currentConnection->copy($file, $dest . '/'. $fileName);
                } else {
                    $this->putFile($dest . '/'. $fileName, $sourceConnection->get($file));
                }
            }
            return $this;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Write to file
     * @param $file
     * @param $content
     *
     * @return boolean
     */
    public function putFile($file, $content)
    {
        return $this->currentConnection->put($file, $content);
    }

    /**
     * Get full path of a connection
     *
     * @param $file
     * @return string
     * @throws \Exception
     */
    public function getFullPath($file)
    {
        try {
            return $this->currentConnection->getDriver()->getAdapter()->getPathPrefix() . $file;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }


    /**
     * Check if path exist
     * @param $path
     * @return bool
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function exists($path)
    {
        try {
            return $this->currentConnection->exists($path);
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Delete directory or file
     * @param $path
     * @return $this
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function delete($path)
    {
        try {
            $this->currentConnection->delete($path);
            $this->currentConnection->deleteDirectory($path);
            return $this;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Remove all files within a given directory
     *
     * @param $path
     * @return $this
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function emptyDir($path)
    {
        try {
            $files = $this->getFiles($path . '/' . '*');
            foreach ($files as $file) {
                $this->delete($file);
            }
            return $this;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Get Latest file of a directory
     * Support Regex on file name
     *
     * @param $path
     * @return string
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getLatestFile($path)
    {
        /** @var \Carbon\Carbon $latestDate */
        try {
            $latestDate = null;
            $latestFileName = null;
            $this->scanFiles($path, function ($fileName) use (
                &$latestDate,
                &$latestFileName
            ) {
                $currentTime = Carbon::createFromTimestamp($this->currentConnection->lastModified($fileName));
                if ($latestDate === null || $latestDate->lt($currentTime)) {
                    $latestDate = $currentTime;
                    $latestFileName = $fileName;
                }
            });
            return $latestFileName;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Make directory, will create full path if not exist
     * @param $path
     *
     * @return $this
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function makeDir($path)
    {
        try {
            $this->currentConnection->makeDirectory($path);
            return $this;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Read file
     * @param $file
     * @return resource|null
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function read($file)
    {
        try {
            return $this->currentConnection->readStream($file);
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Get Files
     * @param $path
     * @param $isCaseSensitive
     * @param $recursive
     * @return array
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getFiles($path, $isCaseSensitive = false, $recursive = false)
    {
        return $this->scanFiles($path, null, $isCaseSensitive, $recursive);
    }

    /**
     * Scan path
     *
     * @param $path
     * @param $callBack
     * @param $isCaseSensitive
     * @param $recursive
     *
     * @return array
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function scanFiles($path, $callBack = null, bool $isCaseSensitive = false, bool $recursive = false)
    {
        try {
            $directory = pathinfo($path, PATHINFO_DIRNAME);
            $fileNames = $this->currentConnection->files($directory, $recursive);
            $result = [];
            $scanFlag = $isCaseSensitive ? null : FNM_CASEFOLD;
            foreach ($fileNames as $fileName) {
                if (!fnmatch($path, $fileName, $scanFlag)) {
                    continue;
                }
                if ($callBack === null) {
                    $result[] = $fileName;
                    continue;
                }
                $current = call_user_func($callBack, $fileName);
                if ($current !== null) {
                    $result[] = $current;
                }
            }
            return $result;
        } catch (\Exception $e) {
            $this->dispatchEventError($e);
        }
    }

    /**
     * Get file connection
     * @param $name
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    private function getConnection($name)
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $connection */
        $connection = $this->storage::disk($name);
        if ($connection instanceof FilesystemAdapter) {
            $adapter = $connection->getDriver()->getAdapter();
            $realAdapter = $adapter;
            if ($adapter instanceof CachedAdapter) {
                $realAdapter = $adapter->getAdapter();
            }

            //Remote Connection
            if ($realAdapter instanceof SftpAdapter) {
                /** @var \phpseclib\Net\SFTP $adapterConnection */
                $adapterConnection = $realAdapter->getConnection();
                try {
                    $adapterConnection->ping();
                } catch (\ErrorException $e) {
                    if ($e->getMessage() === 'Connection closed by server') {
                        $realAdapter->disconnect();
                    }
                }
                $connection = $this->storage::disk($name);
            }
        }
        return $connection;
    }

    /**
     * Dispatch file system error
     * @param \Exception $exception
     * @return $this
     * @throws \Exception | \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function dispatchEventError(\Exception $exception)
    {
        event(new FileSystemFailed($this->currentConnectionName, $exception));
        throw $exception;
    }
}
