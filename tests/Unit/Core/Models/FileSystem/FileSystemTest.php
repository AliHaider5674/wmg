<?php
namespace Tests\Unit\Core\Models\FileSystem;

use App\Services\FileSystemService;
use Tests\TestCase;

/**
 * Test File System
 *
 * Class FileSystemTest
 * @category WMG
 * @package  Tests\Unit\Mdc\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FileSystemTest extends TestCase
{
    /** @var FileSystemService */
    protected $storage;
    public function setUp():void
    {
        parent::setUp();
        $this->storage = app()->make(FileSystemService::class);
    }


    public function testCreatePaths()
    {
        $path = 'this/is/a/very/long/path';
        $this->storage->useConnection('mes_local')
            ->makeDir($path);
        $this->assertTrue($this->storage->exists($path));
        $this->storage->delete($path);
        $this->assertFalse($this->storage->exists($path));
    }
}
