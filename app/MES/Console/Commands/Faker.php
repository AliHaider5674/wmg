<?php

namespace App\MES\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Symfony\Component\Console\Input\InputOption;
use App\MES\Faker\AckFaker;
use App\MES\Faker\ShipmentFaker;

/**
 * Create a fake MES file
 *
 * Class Fulfillment
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Faker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:mes:fake {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create fake shipment by given order number';

    /**
     * @var array
     */
    private array $fakers;

    /**
     * Faker constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption('orders', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'list of order ids');
        $this->addOption('debug', 'd', InputOption::VALUE_OPTIONAL, 'Is show debug call stack.', false);
        $this->addOption('addition', 'a', InputOption::VALUE_OPTIONAL, 'Addition.', false);
    }

    /**
     * Fake MES files
     *
     * @param ShipmentFaker $shipmentFaker
     * @param AckFaker      $ackFaker
     *
     * @return void
     */
    public function handle(ShipmentFaker $shipmentFaker, AckFaker $ackFaker)
    {
        $env = App::environment();
        $debug = config('app.debug');
        $type = $this->argument('type');

        if ($env == 'production' || !$debug) {
            $this->error('Unable to run faker in production or debug is off.');
            return;
        }

        $this->fakers = [];
        $this->fakers['shipment'] = $shipmentFaker;
        $this->fakers['ack'] = $ackFaker;

        if (!isset($this->fakers[$type])) {
            $this->error('Unsupported faker.');
            $this->line('Only supports '. implode(',', array_keys($this->fakers)));
            return;
        }

        $orderIds = $this->option('orders');
        $orders = Order::whereIn('order_id', $orderIds)->get();

        if ($orders->count()<=0) {
            $this->error('No order found.');
            return;
        }

        try {
            $result = $this->fakers[$type]->fake($orders, $this->option('addition'));
            $this->line('Output file to '. $result['file']);
            $this->line('Item count: '. $result['count']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            if ($this->option('debug')) {
                $this->error(
                    $e->getTraceAsString()
                );
            }
        }
    }
}
