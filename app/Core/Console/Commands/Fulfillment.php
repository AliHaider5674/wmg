<?php

namespace App\Core\Console\Commands;

use App\Services\ThreadService;
use Illuminate\Console\Command;
use App\Services\WarehouseService;
use App\Exceptions\NoRecordException;
use Symfony\Component\Console\Input\InputOption;
use WMGCore\Services\AppDataService;

/**
 * Do fulfillment operations
 *
 * Class Fulfillment
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Fulfillment extends Command
{
    const APP_DATA_KEY_RUN_FORMAT = 'fulfillment.job.%s.run';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:fulfillment {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Doing fulfillment operations such as order drop, ' .
                                'stock import, shipment import and ack import';

    public function __construct()
    {
        parent::__construct();
        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_NONE,
            'Show debug call stacks'
        );
    }

    /**
     * Handle fulfillment operation
     *
     * @param WarehouseService $warehouseService
     * @param ThreadService    $threadService
     * @param AppDataService   $appDataService
     *
     * @return void
     * @throws \App\Exceptions\ThreadException
     */
    public function handle(
        WarehouseService $warehouseService,
        ThreadService $threadService,
        AppDataService $appDataService
    ): void {
        $type = $this->argument('type');

        if (!in_array($type, $warehouseService->getFulfillmentHandlerTypes(), true)) {
            $this->line('Unsupported type "' . $type . '"');
            $this->line('type options:');
            $this->line(implode(',', $warehouseService->getFulfillmentHandlerTypes()));

            return;
        }

        $processId = $threadService->startThread('fulfillment.' . $type, 1);

        foreach ($warehouseService->getHandlers($type) as $handler) {
            $appDataKey = sprintf(self::APP_DATA_KEY_RUN_FORMAT, $type);
            $appDataService->update($appDataKey, 'start');

            try {
                $warehouseService->callHandler($handler);
                $appDataService->update($appDataKey, 'success');
                $classParts = explode("\\", get_class($handler));
                $baseClassName = array_pop($classParts);
                $this->line(
                    sprintf(
                        '%s handler was processed for class %s',
                        ucwords($type),
                        $baseClassName
                    )
                );
            } catch (\Exception $exception) {
                $appDataService->update($appDataKey, 'error');

                if ($this->hasOption('debug') && $this->option('debug')) {
                    $this->line($exception->getTraceAsString());
                }

                $this->error($exception->getMessage());
            }
        }

        $threadService->finishThread($processId);
    }
}
