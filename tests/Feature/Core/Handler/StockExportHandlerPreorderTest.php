<?php

namespace Tests\Feature\Core\Handler;

use App\Core\Handlers\StockExportHandler;
use App\Models\StockItem;
use App\Services\WarehouseService;
use Carbon\Carbon;
use PHPUnit\Exception;
use Tests\Feature\WarehouseTestCase;
use App\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Mockery as M;
use App\Models\Service\ModelBuilder\StockBuilder;
use \App\Models\Service\Model\Stock;

/**
 * Class StockExportHandlerPreorderTest
 * @package Tests\Feature\Core\Handler
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class StockExportHandlerPreorderTest extends WarehouseTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->warehouseService = app()->make(WarehouseService::class);

        $this->productSkus = array();

        //set up preorder products
        $this->activePreorderProducts = Product::factory()
            ->count(5)
            ->state(new Sequence(
                ['preorder' => Carbon::now()->addDays(30)->toDateTimeString()],
                ['preorder' => Carbon::now()->addDays(60)->toDateTimeString()]
            ))
            ->create()->each(
                function ($product) {
                    StockItem::factory()->create(
                        [
                            'sku' => $product->sku,
                            'source_id' => 'test'
                        ]
                    );

                    $this->productSkus['activePreorder'][] = $product->sku;
                }
            );

        //setup available products
        $this->availableProducts = Product::factory()
            ->count(10)
            ->state(new Sequence(
                ['preorder' => Carbon::now()->subDays(30)->toDateTimeString()],
                ['preorder' => Carbon::now()->subDays(60)->toDateTimeString()],
                ['preorder' => null]
            ))
            ->create()->each(
                function ($product) {
                    StockItem::factory()->create(
                        [
                            'sku' => $product->sku,
                            'source_id' => 'test'
                        ]
                    );

                    $this->productSkus['available'][] = $product->sku;
                }
            );
    }

    /**
     * testSuccessfulExcludeActivePreorderFromInventoryUpdate
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function testSuccessfulExcludeActivePreorderFromInventoryUpdate()
    {
        $availableProductSkus = $this->productSkus['available'];

        $stockBuilder =  M::mock(StockBuilder::class)->makePartial();

        $stockBuilder
            ->shouldReceive('build')->withArgs(
                function ($stockItems, $sourceId) use ($availableProductSkus) {
                    try {
                        $stockItemsSkus = $stockItems->pluck('sku')->all();
                        $this->assertEqualsCanonicalizing($stockItemsSkus, $availableProductSkus);
                    } catch (Exception $e) {
                        die($e->getMessage());
                    }
                    return true;
                }
            )->andReturn(new Stock());

        $this->app->instance(StockBuilder::class, $stockBuilder);

        $this->warehouseService->callHandler($this->app->make(StockExportHandler::class));
        $this->assertDatabaseCount('stock_items', 15);
    }
}
