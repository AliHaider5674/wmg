<?php declare(strict_types=1);

namespace App\MES\Configurations;

/**
 * Class FlatOrderConfig
 * @package App\MES\Configurations
 */
class FlatOrderConfig
{
    /**
     * @var string
     */
    public $tmpDir;

    /**
     * @var string
     */
    public $liveDir;

    /**
     * @var string
     */
    public $remoteConnection;

    /**
     * @var string
     */
    public $localConnection;

    /**
     * FlatOrderConfig constructor.
     * @param string $tmpDir
     * @param string $liveDir
     * @param string $remoteConnection
     * @param string $localConnection
     */
    public function __construct(
        string $tmpDir,
        string $liveDir,
        string $remoteConnection,
        string $localConnection,
    ) {
        $this->tmpDir = $tmpDir;
        $this->liveDir = $liveDir;
        $this->remoteConnection = $remoteConnection;
        $this->localConnection = $localConnection;
    }
}
