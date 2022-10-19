<?php

namespace App\Core\Handlers;

use App\Core\Handlers\IO\IOInterface;
use App\Core\Services\EventService;
use App\Models\Service\ModelBuilder\StockBuilder;
use App\Models\StockItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \WMGCore\Services\ConfigService;

/**
 * StockExportHandler
 *
 * @category App\Models\Warehouse\Handler
 * @package  App\Models\Warehouse\Handler
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class StockExportHandler extends AbstractHandler
{

    const CONFIG_ARTISTS_PATH_PATTERN = 'fulfillment.stock.artists.%s';

    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var StockBuilder
     */
    private $stockBuilder;
    private ConfigService $configService;

    /**
     * StockExportHandler constructor.
     *
     * @param IOInterface $ioAdapter
     * @param Log             $logger
     * @param EventService    $eventService
     * @param StockBuilder    $stockBuilder
     */
    public function __construct(
        IOInterface $ioAdapter,
        Log $logger,
        EventService $eventService,
        StockBuilder $stockBuilder,
        ConfigService $configService
    ) {
        parent::__construct($ioAdapter, $logger);

        $this->eventService = $eventService;
        $this->stockBuilder = $stockBuilder;
        $this->configService = $configService;
    }

    /**
     * @var int
     */
    private const DEFAULT_EXPORT_SIZE = 500;

    /**
     * @var string
     */
    protected $name = 'stock_export';

    /**
     * Process stock
     *
     * @return void
     */
    public function handle()
    {
        $sourceIds = StockItem::groupBy('source_id')
            ->select('source_id')
            ->pluck('source_id');

        foreach ($sourceIds as $sourceId) {
            $query = StockItem::query()
                ->select([
                    'stock_items.id',
                    'stock_items.sku',
                    'stock_items.qty',
                    'stock_items.source_id',
                    'stock_items.created_at',
                    'stock_items.updated_at'
                ])
                ->leftJoin(
                    'stock_items_history',
                    'stock_items.id',
                    '=',
                    'stock_items_history.parent_id'
                );

            //limit by artists lists
            $artistNames = $this->configService
                ->getJson(
                    sprintf(
                        self::CONFIG_ARTISTS_PATH_PATTERN,
                        strtoLower($sourceId)
                    )
                );

            if (!empty($artistNames)) {
                $query->leftJoin(
                    'products',
                    'stock_items.sku',
                    '=',
                    'products.sku'
                );

                $query->whereIn('products.artist_name', $artistNames);
            }

            //exclude active preorder products
            $query->leftJoin(
                'products as preorder_products',
                'stock_items.sku',
                '=',
                'preorder_products.sku'
            );

            $query->where('source_id', $sourceId)
                ->where('stock_items.updated_at', '>', Carbon::now()->subDay()->toDateTimeString())
                ->where(function ($query) {
                    $query->whereNull('stock_items_history.qty')
                        ->whereColumn('stock_items.qty', '<>', 'stock_items_history.qty', 'or');
                })
                ->where(function ($query) {
                    $query->whereNull('preorder_products.preorder')
                        ->orWhere('preorder_products.preorder', '<', Carbon::now('UTC'));
                })
                ->chunkById(
                    self::DEFAULT_EXPORT_SIZE,
                    function ($stockItems) use ($sourceId) {
                        $data = array();
                        foreach ($stockItems as $stockItem) {
                            $data[] = [
                                'parent_id' => $stockItem->id,
                                'qty' => (float)$stockItem->qty,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                        }

                        if (!empty($data)) {
                            DB::table('stock_items_history')->upsert(
                                $data,
                                ['parent_id'],
                                ['qty', 'updated_at']
                            );
                        }

                        $this->eventService->dispatchEvent(
                            EventService::EVENT_SOURCE_UPDATE,
                            $this->stockBuilder->build($stockItems, $sourceId)
                        );
                    },
                    'stock_items.id',
                    'id'
                );
        }
    }

    /**
     * rollbackItem
     * @param $object
     * @param array ...$args
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function rollbackItem($object, ...$args): void
    {
    }

    public function validate()
    {
        return true;
    }
}
