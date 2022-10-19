<?php

namespace App\Console\Commands;

use App\Exceptions\NoRecordException;
use Illuminate\Console\Command;
use App\Core\Services\EventService;
use App\Models\Service;
use Symfony\Component\Console\Input\InputOption;
use App\Core\Services\ExternalService;

/**
 * Add or remove services
 *
 * Class Fulfillment
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Services extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or remove services';

    protected $warehouseService;

    protected $supportActions = [
        'add',
        'remove',
        'ls',
        'events'
    ];


    public function __construct()
    {
        parent::__construct();

        $this->addArgument(
            'action',
            InputOption::VALUE_REQUIRED,
            implode(',', $this->supportActions)
        );

        $this->addOption(
            'id',
            'i',
            InputOption::VALUE_REQUIRED,
            'ID of the service'
        );

        $this->addOption(
            'name',
            't',
            InputOption::VALUE_OPTIONAL,
            'Name of the service'
        );

        $this->addOption(
            'url',
            'u',
            InputOption::VALUE_OPTIONAL,
            'Name of the service',
            ''
        );

        $this->addOption(
            'rules',
            'r',
            InputOption::VALUE_OPTIONAL,
            'Event rules',
            null
        );

        $this->addOption(
            'client',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Api client, support mom only at this moment'
        );

        $this->addOption(
            'events',
            'e',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Events that this service subscribes to'
        );

        $this->addOption(
            'addition',
            'a',
            InputOption::VALUE_OPTIONAL,
            'Addition',
            null
        );
    }

    /**
     *
     * @return void
     */
    public function handle(
        ExternalService $manager,
        EventService $eventManager
    ) {
        try {
            $action = $this->argument('action');
            if (!in_array($action, $this->supportActions)) {
                $this->line('Unspport action "'. $action . '"');
                $this->line('type options:');
                $this->line(implode(',', $this->supportActions));
                return;
            }
            switch ($action) {
                case 'add':
                    $manager->addService(
                        $this->option('id'),
                        $this->option('name'),
                        $this->option('url'),
                        $this->option('client'),
                        json_decode($this->option('rules'), true) ?? [],
                        $this->option('events'),
                        json_decode($this->option('addition'), true) ?? [],
                    );
                    $this->line('Service is added.');
                    break;
                case 'remove':
                    $this->removeService($this->option('id'));
                    $this->line('Service is removed.');
                    break;
                case 'ls':
                    $this->list();
                    break;
                default:
                    $this->listEvents($eventManager);
            }
        } catch (\Exception $e) {
            $this->line($e->getMessage());
        }
    }

    /**
     * List events
     *
     * @return void
     */
    private function listEvents(EventService $eventManager)
    {
        $rows=[];
        foreach ($eventManager->getEvents() as $event => $description) {
            $rows[] = [$event, $description];
        }
        $this->table(['event', 'description'], $rows);
    }

    /**
     * List services
     *
     * @return void
     */
    private function list()
    {
        $services = Service::join('service_events as e', 'services.id', '=', 'parent_id')
            ->groupBy('services.id')
            ->selectRaw('name, client, GROUP_CONCAT(e.event SEPARATOR "\n") as events')
            ->get();
        $rows = $services->toArray();
        $this->table(['name', 'client', 'events'], $rows);
    }

    /**
     * Remove service
     * @param $appId
     * @return void
     * @throws \App\Exceptions\NoRecordException
     * @throws \Exception
     */
    protected function removeService($appId)
    {
        /** @var Service $service */
        $service = Service::where('app_id', $appId)->first();
        if (!$service) {
            throw new NoRecordException('App '. $appId. ' not found.');
        }
        $service->delete();
    }
}
