<?php
namespace App\Services;

use App\Models\Thread;
use App\Exceptions\ThreadException;
use App\Services\AlertEventService;
use App\Models\AlertEvent;
use WMGCore\Services\ConfigService;

/**
 * A services that monitoring threads
 *
 * Class WarehouseService
 * @category WMG
 * @package  App\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ThreadService
{
    const DEFAULT_THREAD_LIFETIME = 720; //MINUTES, 12 hours
    const DEFAULT_MAX_THREAD_EXECUTION_TIME = 60;//MINUTES
    private $configService;
    private $alertEventService;
    public function __construct(ConfigService $configService, AlertEventService $alertEventService)
    {
        $this->configService = $configService;
        $this->alertEventService = $alertEventService;
    }

    /**
     * Start a new thread
     * @param $name
     * @param int $maxThread
     * @return int           thread id
     * @throws \App\Exceptions\ThreadException
     */
    public function startThread($name, $maxThread = 1)
    {
        $threadCount = Thread::where('name', '=', $name)->count();
        if ($threadCount>=$maxThread) {
            throw new ThreadException("Max thread is reach for $name", ThreadException::MAX_THREAD_REACH);
        }
        $newThread = new Thread();
        $newThread->name = $name;
        $newThread->save();
        return $newThread->id;
    }

    /**
     * Finish a thread
     * @param $processId
     * @return $this
     * @throws \App\Exceptions\ThreadException
     */
    public function finishThread($processId)
    {
        $thread = Thread::where('id', '=', $processId)->first();
        if (!$thread) {
            throw new ThreadException("Thread is not found for id $processId", ThreadException::THREAD_NOT_EXIST);
        }
        $thread->delete();
        return $this;
    }

    /**
     * Delete old threads
     * @return $this
     */
    public function cleanOldThreads()
    {
        $lifetime = $this->configService->get('thread.lifetime', self::DEFAULT_THREAD_LIFETIME);
        if ($lifetime >= self::DEFAULT_THREAD_LIFETIME) {
            Thread::whereRaw("created_at < DATE_SUB(NOW(), INTERVAL $lifetime MINUTE)")->delete();
        }
        return $this;
    }

    /**
     * Send thread alerts
     *
     * @return void
     */
    public function sendThreadAlert()
    {
        $executionTime =  $this->configService->get(
            'thread.default.max.execution.time',
            self::DEFAULT_MAX_THREAD_EXECUTION_TIME
        );
        $threadNames = Thread::whereRaw("created_at < DATE_SUB(NOW(), INTERVAL $executionTime MINUTE)")
                ->pluck('name')->toArray();
        if (!empty($threadNames)) {
            $this->alertEventService->addEvent(
                'Single thread run for too long',
                implode(',', $threadNames),
                AlertEvent::TYPE_INTERNAL_ERROR,
                AlertEvent::LEVEL_CRITICAL
            );
        }
    }
}
