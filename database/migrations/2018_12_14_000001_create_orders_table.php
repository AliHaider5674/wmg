<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateOrdersTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateOrdersTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'orders';

    /**
     * Run the migrations.
     * @table orders
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
            $table->unsignedTinyInteger('status')->default('0')->comment('0 IS RECEIVED, 1 is DROPPED, 2 is ERROR');
            $table->string('sales_channel');
            $table->string('request_id', 45)->comment('EXTERNAL REQUEST ID');
            $table->string('order_id', 45)->comment('EXTERNAL_ORDER_ID');
            $table->string('gift_message')->nullable();
            $table->unsignedInteger('drop_id')->nullable();
            $table->string('shipping_method', 45)->nullable();
            $table->string('customer_id', 45)->nullable();
            $table->string('customer_reference', 45)->nullable();
            $table->string('vat_country', 45)->nullable();
            $table->text('custom_attributes')->nullable(true);
            $table->index(["drop_id"], 'SHIPMENT_REQUEST_SHIPMENT_DROP_FK_idx');

            $table->unique(["sales_channel", "request_id"], 'SHIPMENT_REQUESTS_UNIQUE_REQUEST');
            $table->timestamps();


            $table->foreign('drop_id', 'SHIPMENT_REQUEST_SHIPMENT_DROP_FK_idx')
                ->references('id')->on('order_drops')
                ->onDelete('set null')
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
