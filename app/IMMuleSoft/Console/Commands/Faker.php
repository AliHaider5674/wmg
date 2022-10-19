<?php

namespace App\IMMuleSoft\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Symfony\Component\Console\Input\InputOption;
use App\IMMuleSoft\Faker\ShipmentFaker;

/**
 * Class Faker
 * @package App\Console\Commands
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Faker extends Command
{
    const FAKER_SHIPMENT = 'shipment';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:immulesoft:fake {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create fake data for IM MuleSoft interfaces, e.g shipments';
    private array $fakers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption(
            'orders',
            'o',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'list of order ids'
        );

        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Is show debug call stack.',
            false
        );

        $this->addOption(
            'addition',
            'a',
            InputOption::VALUE_OPTIONAL,
            'Addition.',
            false
        );

        $this->addOption(
            'backorder',
            'b',
            InputOption::VALUE_OPTIONAL,
            'Enable backorder.',
            false
        );

        $this->addOption(
            'backorder_sku',
            'bs',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'list of backorder skus',
            array()
        );

        $this->addOption(
            'backorder_order_item_ids',
            'boi',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'list of backorder order item ids',
            array()
        );
    }

    /**
     * Execute the console command.
     *
     * @param ShipmentFaker $shipmentFaker
     * @return int
     */
    public function handle(ShipmentFaker $shipmentFaker): int
    {
        $this->setupFakers(
            $shipmentFaker
        );

        if (!$this->isCommandAllowed()) {
            return 1;
        }

        $type = $this->argument('type');
        if (!$this->isFakerTypeAllowed($type)) {
            return 1;
        }

        $orderIds = $this->option('orders');
        $orders = Order::whereIn('order_id', $orderIds)->get();

        if ($orders->count()<=0) {
            $this->error('No order found.');
            return false;
        }

        $options = $this->options();

        try {
            $result = $this->fakers[$type]->fake($orders, $options);
            $this->line('Item count: '. $result['count']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            if ($this->option('debug')) {
                $this->error(
                    $e->getTraceAsString()
                );
            }
        }

        return 0;
    }

    /**
     * isCommandAllowed
     * @return bool
     */
    public function isCommandAllowed()
    {
        $env = App::environment();
        $debug = config('app.debug');

        if ($env == 'production' || !$debug) {
            $this->error('Unable to run faker in production or debug is off.');
            return false;
        }

        return true;
    }

    /**
     * isFakerTypeAllowed
     * @param string $type
     * @return bool
     */
    public function isFakerTypeAllowed(string $type): bool
    {
        if (!isset($this->fakers[$type])) {
            $this->error('Unsupported faker.');
            $this->line('Only supports '. implode(',', array_keys($this->fakers)));
            return false;
        }

        return true;
    }

    private function setupFakers(
        ShipmentFaker $shipmentFaker
    ) {
        $this->fakers = [];
        $this->fakers[self::FAKER_SHIPMENT] = $shipmentFaker;
    }
}
