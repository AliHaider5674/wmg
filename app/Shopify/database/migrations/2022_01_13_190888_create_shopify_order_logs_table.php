<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateShopifyFulfillmentFetchOrderTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class CreateShopifyOrderLogsTable extends Migration
{
    const TABLE_NAME = 'shopify_order_logs';
    /**
     * Run the migrations.
     * @table regions
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            return;
        }
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('parent_id');
            $table->string('type', 255);
            $table->string('message', 255);
            $table->string('status', 255);
            $table->nullableTimestamps();

            $table->foreign('parent_id', 'SHOPIFY_ORDER_SHOPIFY_ORDER_LOG_IDX')
                ->references('id')
                ->on('shopify_orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
