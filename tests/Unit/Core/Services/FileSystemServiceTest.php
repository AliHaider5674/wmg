<?php

namespace Tests\Unit\Core\Services;

use App\Services\FileSystemService;
use Tests\TestCase;

/**
 * Test file system service
 *
 * Class FileSystemServiceTest
 * @category WMG
 * @package  Tests\Unit\Core\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FileSystemServiceTest extends TestCase
{
    /**
     * @var FileSystemService
     */
    private FileSystemService $fileSystemService;

    public function setUp():void
    {
        parent::setUp();
        $this->fileSystemService = $this->app->make(FileSystemService::class);
    }

    public function testFileCaseSensitiveScan()
    {
        $this->fileSystemService->useConnection('local_test');
        $this->fileSystemService->putFile('test.TXT', 'test');
        $this->fileSystemService->putFile('something.txt', 'test');
        $this->fileSystemService->putFile('next/next.txt', 'test');
        $files = $this->fileSystemService
            ->getFiles('*.txt', true);
        $this->assertCount(1, $files);

        $files = $this->fileSystemService
            ->getFiles('*.txt', false);
        $this->assertCount(2, $files);

        $files = $this->fileSystemService
            ->getFiles('next/*.txt', false);
        $this->assertCount(1, $files);
    }
}
