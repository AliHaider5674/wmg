<?php

namespace App\Console\Commands;

use App\Models\AlertEvent;
use App\Services\ConfigService;
use App\Services\ThreadService;
use Illuminate\Console\Command;
use App\Mail\Alert as MailAlert;
use Illuminate\Support\Facades\Mail;

/**
 * Send out alert emails
 *
 * Class Fulfillment
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Thread extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:thread {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage alerts. 
        clean: clean threads that ran for too long
        alert: send out alerts for threads that ran longer that usual
    ';


    /**
     * Send alert emails
     * @param ThreadService $threadService
     * @return void
     */
    public function handle(ThreadService $threadService)
    {
        $action = $this->argument('action');
        switch (strtolower($action)) {
            case 'clean':
                $threadService->cleanOldThreads();
                break;
            case 'alert':
                $threadService->sendThreadAlert();
                break;
            default:
                $this->error('Unsupported action.');
                break;
        }
    }
}
