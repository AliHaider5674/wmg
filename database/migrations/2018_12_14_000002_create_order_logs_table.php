<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateOrderLogsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateOrderLogsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'order_logs';

    /**
     * Run the migrations.
     * @table order_logs
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
            $table->unsignedInteger('parent_id');
            $table->string('message');
            $table->tinyInteger('status')->default('0')->comment('0 IS RECEIVED, 1 is DROPPED, 2 is ERROR');

            $table->index(["parent_id"], 'SHIPMENT_REQUEST_LOG_SHIPMENT_REQUEST_FK_idx');
            $table->timestamps();


            $table->foreign('parent_id', 'SHIPMENT_REQUEST_LOG_SHIPMENT_REQUEST_FK_idx')
                ->references('id')->on('orders')
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
