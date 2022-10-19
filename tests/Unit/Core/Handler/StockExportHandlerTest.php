<?php

namespace Tests\Unit\Core\Handler;

use App\Core\Handlers\StockExportHandler;
use App\Models\StockItem;
use App\Services\WarehouseService;
use Tests\Feature\WarehouseTestCase;

/**
 * Class StockExportHandlerTest
 * @package Tests\Unit\Core\Handler
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class StockExportHandlerTest extends WarehouseTestCase
{
    const NUMBER_OF_STOCK_ITEMS = 1000;

    /**
     * setUp
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->stockItems = StockItem::factory()->count(self::NUMBER_OF_STOCK_ITEMS)->create(
            [
                'source_id' => 'test'
            ]
        );
        $this->warehouseService = app()->make(WarehouseService::class);
    }

    /**
     * testHandle
     */
    public function testSuccessfulExportAllStockItems()
    {
        $this->warehouseService->callHandler($this->app->make(StockExportHandler::class));
        $this->assertDatabaseCount('stock_items_history', self::NUMBER_OF_STOCK_ITEMS);
    }
}
