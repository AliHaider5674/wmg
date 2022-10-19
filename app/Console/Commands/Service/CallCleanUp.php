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
use Carbon\Carbon;

/**
 * Call clean up
 *
 * Class Mom
 * @category WMG
 * @package  App\Console\Commands\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CallCleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:service:call:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old calls';

    const DEFAULT_MAX_ATTEMPTS = 3;

    public function __construct()
    {
        parent::__construct();
        $this->addOption(
            'hour',
            'o',
            InputOption::VALUE_REQUIRED,
            'Cleanup calls older than given days'
        );
    }

    /**
     * Retry calls
     *
     * @return void
     */
    public function handle()
    {
        $hours = $this->option('hour');
        ServiceEventCall::where('created_at', '<', Carbon::now('UTC')->subHours($hours)->toDateTimeString())
                        ->whereNotIn('status', [
                            ServiceEventCall::STATUS_BEING_DELIVERED,
                            ServiceEventCall::STATUS_HARD_ERROR])->delete();
        $this->line(sprintf('Calls that are old than %s hours have been cleanup.', $hours));
    }
}
