<?php

namespace App\Console\Commands\Mom;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Artisan;
use MomApi\Client;
use App\Mom\Models\Config;

/**
 * Register Mom events
 *
 * Class Mom
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Register extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:mom:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register Mom events';


    /**
     * Register and deregister events
     *
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('mom:register');
        $this->line('Registered.');
    }
}
