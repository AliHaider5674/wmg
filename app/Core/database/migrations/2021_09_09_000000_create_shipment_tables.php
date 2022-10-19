<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Core\Enums\WarehouseStatus;

/**
 * Class CreateCountryRegionsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateShipmentTables extends Migration
{
    const SHIPMENT_TABLE_NAME = 'shipments';
    const SHIPMENT_ITEM_TABLE_NAME = 'shipment_items';
    /**
     * Run the migrations.
     * @table regions
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists(self::SHIPMENT_TABLE_NAME);
        Schema::dropIfExists(self::SHIPMENT_ITEM_TABLE_NAME);
        Schema::create(self::SHIPMENT_TABLE_NAME, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->string('tracking_number', 255);
            $table->string('carrier', 255);
            $table->nullableTimestamps();
            $table->foreign('order_id', 'SHIPMENT_ORDER_IDX')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });




        Schema::create(self::SHIPMENT_ITEM_TABLE_NAME, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('parent_id');
            $table->string('order_item_id', 255);
            $table->decimal('quantity', 12, 4)->default(1);
            $table->nullableTimestamps();
            $table->foreign('parent_id', 'SHIPMENT_ITEM_SHIPMENT_IDX')
                ->references('id')
                ->on('shipments')
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
        Schema::dropIfExists(self::SHIPMENT_ITEM_TABLE_NAME);
        Schema::dropIfExists(self::SHIPMENT_TABLE_NAME);
    }
}
