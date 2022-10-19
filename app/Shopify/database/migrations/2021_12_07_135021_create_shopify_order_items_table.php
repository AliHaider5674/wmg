<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateShopifyOrdersTable
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class CreateShopifyOrderItemsTable extends Migration
{
    const TABLE_NAME = 'shopify_order_items';
    /**
     * Run the migrations.
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
            $table->tinyInteger('status')->default(0);
            $table->integer('parent_id')->unsigned();
            $table->string('sku');
            $table->string('shopify_line_id');
            $table->float('qty')->default(1);
            $table->nullableTimestamps();
            $table->foreign('parent_id', 'SHOPIFY_ORDER_ITEM_IDX')
                ->references('id')
                ->on('shopify_orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('sku', 'SHOPIFY_ORDER_ITEM_PRODUCT_IDX')
                ->references('sku')
                ->on('products')
                ->onDelete('no action')
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
