<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateShopifyFailedFulfillmentOrdersTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class CreateShopifyFailedFulfillmentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_failed_fulfillment_orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fulfillment_order_id');
            $table->integer('service_id', false)->unsigned();
            $table->tinyInteger('attempts')
                ->default(0);

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_failed_fulfillment_orders');
    }
}
