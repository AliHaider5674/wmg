<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Update stock table, by adding keys
 *
 * Class UpdateStockItemsTable
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class UpdateStockItemsTable extends Migration
{
    private $tableName = 'stock_items';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('source_id')->default('US')->after('qty');
            $table->unique(['source_id', 'sku'], 'UNIQUE_STOCK_SKU_SOURCE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn($this->tableName, 'source_id')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropUnique('UNIQUE_STOCK_SKU_SOURCE');
                $table->dropColumn('source_id');
            });
        }
    }
}
