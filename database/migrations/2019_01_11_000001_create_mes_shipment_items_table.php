<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create MES shipment items table
 *
 * Class CreateMesShipmentItemsTable
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateMesShipmentItemsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'mes_shipment_items';

    /**
     * Run the migrations.
     * @table mes_shipment_item
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tableName)) {
            return;
        }
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('item_id')
                ->unsigned()
                ->nullable(false);

            $table->index(["item_id"], 'FK_MES_SHIPMENT_ITEM_ORDER_ITEM_idx');

            $table->index(["parent_id"], 'FK_MES_SHIPMENT_ITEM_MES_SHIPMENT_idx');
            $table->timestamps();


            $table->foreign('item_id', 'FK_MES_SHIPMENT_ITEM_ORDER_ITEM_idx')
                ->references('id')->on('order_items')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('parent_id', 'FK_MES_SHIPMENT_ITEM_MES_SHIPMENT_idx')
                ->references('id')->on('mes_shipments')
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
        Schema::dropIfExists($this->tableName);
    }
}
