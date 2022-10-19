<?php
namespace Tests\Unit\Core\Services;

use App\Exceptions\ThreadException;
use App\Services\ThreadService;
use Tests\TestCase;

/**
 * Test thread service
 *
 * Class ThreadServiceTest
 * @category WMG
 * @package  Tests\Unit\Core\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ThreadServiceTest extends TestCase
{
    /** @var ThreadService */
    private $threadService;

    public function setUp():void
    {
        parent::setUp();
        $this->threadService = app()->make(ThreadService::class);
    }


    public function testStandardOperation()
    {
        $processId = $this->threadService->startThread('test');
        $this->threadService->finishThread($processId);
        $this->assertTrue(true);
    }

    public function testStartMultiple()
    {
        $this->threadService->startThread('test', 2);
        $this->threadService->startThread('test', 2);
        $this->assertTrue(true);
    }

    public function testReachMax()
    {
        $this->expectException(ThreadException::class);
        $this->expectExceptionCode(ThreadException::MAX_THREAD_REACH);
        $this->threadService->startThread('test', 2);
        $this->threadService->startThread('test', 2);
        $this->threadService->startThread('test', 2);
    }

    public function testStopNotExistThread()
    {
        $this->expectException(ThreadException::class);
        $this->expectExceptionCode(ThreadException::THREAD_NOT_EXIST);
        $this->threadService->finishThread('132312');
    }
}
