<?php

namespace Tests\Feature\Salesforce;

use App\Catalog\Models\Product;
use App\Core\Handlers\StockExportHandler;
use App\Models\Service\Model\Stock;
use App\Models\Service\ModelBuilder\StockBuilder;
use App\Models\StockItem;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Mockery as M;
use Tests\TestCase;
use WMGCore\Services\ConfigService;
use function app;

/**
 * Class OciStockExportLimitByArtistSuccessTest
 * @package Tests\Feature
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OciStockExportLimitByArtistSuccessTest extends TestCase
{
    const CONFIG_ARTISTS_PATH_PATTERN = 'fulfillment.stock.artists.%s';


    public function setUp(): void
    {
        parent::setUp();

        $fixtures = $this->getFixtures();

        foreach ($fixtures as $fixture) {
            $this->stockItems[] = StockItem::factory()
                ->create(
                    [
                        'source_id' => $fixture['source_id'],
                        'sku' => $fixture['sku'],
                        'qty' => $fixture['qty'],
                        'updated_at' => $fixture['updated_at']
                    ]
                );

            $this->products[] = Product::factory()
                ->create(
                    [
                        'sku' => $fixture['sku'],
                        'name' => $fixture['name'],
                        'artist_name' => $fixture['artist_name']
                    ]
                );
        }
        $this->configService = app()->make(ConfigService::class);
    }

    /**
     * testNoLimitStockExportArtistSuccessful
     *
     * No limitation for any source_id, all stock will be exported
     *
     * @throws BindingResolutionException
     */
    public function testNoLimitStockExportArtistSuccessful()
    {
        $stockBuilder = M::mock(StockBuilder::class);
        $stockBuilder->shouldReceive('build')->withArgs(
            function ($stockItems, $sourceId) {
                $skus = $stockItems->pluck('sku')->all();
                if ('US' == $sourceId) {
                    $this->assertEquals(['054197157783','054197152078', '090317016740'], $skus);
                    return true;
                }

                if ('GNAR' == $sourceId) {
                    $this->assertEquals(['090317015866'], $skus);
                    return true;
                }

                if ('IM' == $sourceId) {
                    $this->assertEquals(['0190295768478','0190295768348', '0075679971524'], $skus);
                    return true;
                }
                return true;
            }
        )->andReturn(new Stock());

        $this->app->instance(StockBuilder::class, $stockBuilder);

        Event::fake();
        $stockExportHandler = app()->make(StockExportHandler::class);
        $stockExportHandler->handle();
    }

    /**
     * testLimitStockExportBySingleArtistSuccessful
     *
     * Limit stock inventory by artists and source_id
     * In this test only inventory for Ed Sheeran can be sent to the US source_id
     * Therefore inventory for Paramore 090317016740 will be excluded from US source_id export
     *
     * No limitation for stock export to other source_ids
     *
     * @throws BindingResolutionException
     */
    public function testLimitStockExportBySingleArtistSuccessful()
    {
        $path = sprintf(
            self::CONFIG_ARTISTS_PATH_PATTERN,
            strtoLower('us')
        );

        //Limit stock export to only Ed Sheeran for US source_id
        $this->configService->update($path, ['Ed Sheeran']);

        $stockBuilder = M::mock(StockBuilder::class);
        $stockBuilder->shouldReceive('build')->withArgs(
            function ($stockItems, $sourceId) {
                $skus = $stockItems->pluck('sku')->all();
                if ('US' == $sourceId) {
                    $this->assertEquals(['054197157783','054197152078'], $skus);
                    return true;
                }

                if ('GNAR' == $sourceId) {
                    $this->assertEquals(['090317015866'], $skus);
                    return true;
                }

                if ('IM' == $sourceId) {
                    $this->assertEquals(['0190295768478','0190295768348', '0075679971524'], $skus);
                    return true;
                }
                return true;
            }
        )->andReturn(new Stock());

        $this->app->instance(StockBuilder::class, $stockBuilder);

        Event::fake();
        $stockExportHandler = app()->make(StockExportHandler::class);
        $stockExportHandler->handle();
    }

    /**
     * testLimitStockExportByMultipleArtistSuccessful
     *
     * Limit stock inventory by artists and source_id
     * In this test only inventory for Ed Sheeran and Paramore can be sent to the US source_id
     *
     * No limitation for stock export to other source_ids
     *
     * @throws BindingResolutionException
     */
    public function testLimitStockExportByMultipleArtistSuccessful()
    {
        $path = sprintf(
            self::CONFIG_ARTISTS_PATH_PATTERN,
            strtoLower('us')
        );

        //Limit stock export to only Ed Sheeran for US source_id
        $this->configService->update($path, ['Ed Sheeran', 'Paramore']);

        $stockBuilder = M::mock(StockBuilder::class);
        $stockBuilder->shouldReceive('build')->withArgs(
            function ($stockItems, $sourceId) {
                $skus = $stockItems->pluck('sku')->all();
                if ('US' == $sourceId) {
                    $this->assertEquals(['054197157783','054197152078', '090317016740'], $skus);
                    return true;
                }

                if ('GNAR' == $sourceId) {
                    $this->assertEquals(['090317015866'], $skus);
                    return true;
                }

                if ('IM' == $sourceId) {
                    $this->assertEquals(['0190295768478','0190295768348', '0075679971524'], $skus);
                    return true;
                }

                return true;
            }
        )->andReturn(new Stock());

        $this->app->instance(StockBuilder::class, $stockBuilder);

        Event::fake();
        $stockExportHandler = app()->make(StockExportHandler::class);
        $stockExportHandler->handle();
    }

    /**
     * testLimitStockExportByMultipleArtistSuccessful
     *
     * Limit stock inventory by artists and source_id
     * In this test only inventory for Ed Sheeran and Paramore can be sent to the US source_id
     *
     * No limitation for stock export to other source_ids
     *
     * @throws BindingResolutionException
     */
    public function testLimitStockExportByMultipleArtistAndMultipleSourcesSuccessful()
    {
        $pathUS = sprintf(
            self::CONFIG_ARTISTS_PATH_PATTERN,
            strtoLower('us')
        );

        $pathIM = sprintf(
            self::CONFIG_ARTISTS_PATH_PATTERN,
            strtoLower('im')
        );

        //Limit stock export to only Ed Sheeran for US source_id
        $this->configService->update($pathUS, ['Ed Sheeran', 'Paramore']);
        $this->configService->update($pathIM, ['Paramore']);

        $stockBuilder = M::mock(StockBuilder::class);
        $stockBuilder->shouldReceive('build')->withArgs(
            function ($stockItems, $sourceId) {
                $skus = $stockItems->pluck('sku')->all();
                if ('US' == $sourceId) {
                    $this->assertEquals(['054197157783','054197152078', '090317016740'], $skus);
                    return true;
                }

                if ('GNAR' == $sourceId) {
                    $this->assertEquals(['090317015866'], $skus);
                    return true;
                }

                if ('IM' == $sourceId) {
                    $this->assertEquals(['0075679971524'], $skus);
                    return true;
                }
                return true;
            }
        )->andReturn(new Stock());

        $this->app->instance(StockBuilder::class, $stockBuilder);

        Event::fake();
        $stockExportHandler = app()->make(StockExportHandler::class);
        $stockExportHandler->handle();
    }

    /**
     * getFixtures
     */
    public function getFixtures(): array
    {
        return [
            [
                'sku' => '054197157783',
                'source_id' => 'US',
                'qty' => 10,
                'updated_at' => Carbon::now(),
                'name' => 'Mathematics Tour Off White T-Shirt (S)',
                'artist_name' => 'Ed Sheeran'
            ],
            [
                'sku' => '054197152078',
                'source_id' => 'US',
                'qty' => 20,
                'updated_at' => Carbon::now(),
                'name' => '2step CD (Signed)',
                'artist_name' => 'Ed Sheeran'
            ],
            [
                'sku' => '090317016740',
                'source_id' => 'US',
                'qty' => 20,
                'updated_at' => Carbon::now(),
                'name' => '3 Bar Logo Flag',
                'artist_name' => 'Paramore'
            ],
            [
                'sku' => '090317015866',
                'source_id' => 'GNAR',
                'qty' => 33,
                'updated_at' => Carbon::now(),
                'name' => 'Black Flag Monumentour T-Shirt (XL)',
                'artist_name' => 'Paramore'
            ],
            [
                'sku' => '0190295768478',
                'source_id' => 'IM',
                'qty' => 33,
                'updated_at' => Carbon::now(),
                'name' => 'Equation Olive T-Shirt (S)',
                'artist_name' => 'New Order'
            ],
            [
                'sku' => '0190295768348',
                'source_id' => 'IM',
                'qty' => 45,
                'updated_at' => Carbon::now(),
                'name' => 'Equation Mug',
                'artist_name' => 'New Order'
            ],
            [
                'sku' => '0075679971524',
                'source_id' => 'IM',
                'qty' => 4,
                'updated_at' => Carbon::now(),
                'name' => 'Playing God',
                'artist_name' => 'Paramore'
            ],
        ];
    }
}
