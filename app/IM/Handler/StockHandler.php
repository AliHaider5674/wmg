<?php

namespace App\IM\Handler;

use App\Core\Handlers\AbstractHandler;
use App\Exceptions\NoRecordException;
use App\Core\Services\EventService;
use App\IM\Handler\IO\ApiStock;
use App\Models\Service\ModelBuilder\SourceParameter;
use App\Models\Service\ModelBuilder\StockBuilder;
use App\Models\AlertEvent;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;

/**
 * Class ApiStockHandler
 *
 * Handle stock import from an API source
 *
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class StockHandler extends AbstractHandler
{
    protected $name = 'apiStock';

    /**
     * StockIO constructor.
     * @param ApiStock $ioAdapter
     * @param Log             $logger
     */
    public function __construct(ApiStock $ioAdapter, Log $logger)
    {
        parent::__construct($ioAdapter, $logger);
    }

    /**
     * Process stock import
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function handle()
    {
        /** @var EventService $eventManager */
        $eventManager = app()->make(EventService::class);
        /** @var StockBuilder $builder */
        $builder = app()->make(StockBuilder::class);

        $this->ioAdapter->start();

        try {
            $this->ioAdapter->receive(function (SourceParameter $parameter) use ($eventManager, $builder) {
                $model = $builder->buildFromSourceParameter($parameter);
                $eventManager->dispatchEvent(EventService::EVENT_SOURCE_UPDATE, $model);
            });
        } catch (NoRecordException $e) {
            $data = [
                'name' => __CLASS__,
                'content' => $e->getMessage(),
                'type' => AlertEvent::TYPE_NO_RECORDS,
                'level' => AlertEvent::LEVEL_MEDIUM
            ];
            $alertEvent = new AlertEvent();
            $alertEvent->fill($data);
            $alertEvent->save();
        }

        $this->ioAdapter->finish();
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function rollbackItem($object, ...$args): void
    {
    }
}
