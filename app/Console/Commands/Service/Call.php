<?php

namespace App\Console\Commands\Service;

use App\Models\ServiceEvent;
use App\Core\Services\EventService;
use App\Models\ServiceEventCall;
use Illuminate\Console\Command;

use MomApi\Client;
use App\Mom\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Call Retry
 *
 * Class Mom
 * @category WMG
 * @package  App\Console\Commands\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Call extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:service:call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Service calls, actions can be count, retry';

    const DEFAULT_MAX_ATTEMPTS = 3;
    const TRUNK_SIZE = 500;

    public function __construct()
    {
        parent::__construct();
        $this->addOption(
            'ids',
            'i',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Call Ids'
        );
        $this->addOption(
            'from',
            'f',
            InputOption::VALUE_OPTIONAL,
            'From Date'
        );
        $this->addOption(
            'end',
            'e',
            InputOption::VALUE_OPTIONAL,
            'End Date'
        );
        $this->addOption(
            'status',
            's',
            InputOption::VALUE_OPTIONAL,
            'Status'
        );
        $this->addOption(
            'max',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Max attempt',
            self::DEFAULT_MAX_ATTEMPTS
        );
        $this->addOption(
            'event',
            't',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Event'
        );
        $this->addOption(
            'dryrun',
            'd',
            InputOption::VALUE_NONE,
            'Dry run'
        );
    }

    /**
     * Retry calls
     *
     * @param \App\Core\Services\EventService $eventManager
     * @return void
     */
    public function handle(EventService $eventManager)
    {
        $eventCallsQuery = ServiceEventCall::query();
        if ($this->option('ids')) {
            $eventCallsQuery =  $eventCallsQuery->whereIn('id', $this->option('ids'));
        } elseif ($this->option('event')) {
            $ids = ServiceEvent::whereIn('event', $this->option('event'))->pluck('id');
            if (count($ids) === 0) {
                $this->error('Invalid event.');
                return;
            }
            $eventCallsQuery = $eventCallsQuery->whereIn('parent_id', $ids);
        }

        $eventCallsQuery->whereIn('status', [
            ServiceEventCall::STATUS_SOFT_ERROR,
            ServiceEventCall::STATUS_HARD_ERROR,
        ]);

        $this->handleOptions($eventCallsQuery);

        $count = $eventCallsQuery->count();
        if ($this->option('dryrun')) {
            $this->line($count . ' calls');
            return;
        }
        $eventCallsQuery->chunk(self::TRUNK_SIZE, function ($eventCalls) use ($eventManager) {
            foreach ($eventCalls as $call) {
                $call->setAttribute('status', ServiceEventCall::STATUS_BEING_DELIVERED);
                $eventManager->queueEventCall($call);
            }
        });
        $this->line($count . ' calls sent to queue');
    }

    private function handleOptions($eventCallsQuery)
    {
        if ($this->option('from')) {
            $eventCallsQuery->where('created_at', '>=', $this->option('from'));
        }

        if ($this->option('end')) {
            $eventCallsQuery->where('created_at', '<=', $this->option('end'));
        }

        if ($this->option('max')) {
            $eventCallsQuery->where('attempts', '<', $this->option('max'));
        }

        if ($this->option('status')) {
            $eventCallsQuery->where('status', '=', $this->option('status'));
        }
    }
}
