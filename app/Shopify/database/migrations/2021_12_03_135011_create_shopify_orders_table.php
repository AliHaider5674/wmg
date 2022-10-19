<?php

use App\Shopify\Enums\ShopifyOrderStatus;
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
class CreateShopifyOrdersTable extends Migration
{
    const TABLE_NAME = 'shopify_orders';
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
            $table->integer('service_id', false)->unsigned();
            $table->tinyInteger('status')->default(ShopifyOrderStatus::FETCHED);
            $table->string('order_id', 255);
            $table->longText('data');
            $table->timestamp('ordered_at');
            $table->nullableTimestamps();

            $table->foreign('service_id', 'SHOPIFY_RAW_ORDER_SERVICE_IDX')
                ->references('id')
                ->on('services')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unique(['order_id', 'service_id'], 'SHOPIFY_UNIQUE_ORDER');
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
