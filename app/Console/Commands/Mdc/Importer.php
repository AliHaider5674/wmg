<?php

namespace App\Console\Commands\Mdc;

use App\Mdc\Importer\OrderImporter;
use Illuminate\Console\Command;

use MomApi\Client;
use App\Mom\Config;
use Symfony\Component\Console\Input\InputOption;

/**
 * Import M1 Orders
 *
 * Class Mom
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Importer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:mdc:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import m1 orders';

    public function __construct()
    {
        parent::__construct();
        $this->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Directory of the order files');
    }

    /**
     * @param \App\Mdc\Importer\OrderImporter $orderImporter
     * @return void
     */
    public function handle(OrderImporter $orderImporter)
    {
        $orderImporter->import($this->option('directory'));
    }
}
