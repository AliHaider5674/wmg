<?php

namespace App\Core\Handlers;

use App\Models\Service\ModelBuilder\SourceParameter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Database StockIO that will simply put stock in the database
 *
 * @category App\Core\Handler
 * @package  App\Core\Handler
 * @license  WMG License
 * @link     http://www.wmg.com
 */
abstract class DatabaseStockHandler extends AbstractHandler
{
    /**
     * Process stock in the database
     *
     * @return void
     */
    public function handle()
    {
        $this->ioAdapter->start();
        $this->ioAdapter->receive(function (SourceParameter $parameter) {
            $data = [];
            $productData = [];
            foreach ($parameter->stockLines as $line) {
                $data[] = [
                    'sku' => $line->sku,
                    'qty' => $line->quantity,
                    'allocated_qty' => $line->allocatedQuantity,
                    'source_id' => $parameter->getSourceId(),
                ];

                if (!empty($line->artistName)) {
                    $productData[] = [
                        'sku' => $line->sku,
                        'name' => '<placeholder>',
                        'artist_name' => $line->artistName,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
            insertOrUpdateSql($data, 'stock_items');

            if (!empty($productData)) {
                $this->insertOrUpdateProductData($productData);
            }
        });
        $this->ioAdapter->finish();
    }

    /**
     * insertOrUpdateProductData
     * @param array $productData
     */
    protected function insertOrUpdateProductData(array $productData)
    {
        DB::table('products')->upsert(
            $productData,
            ['sku'],
            ['artist_name', 'updated_at']
        );
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return true;
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
}
