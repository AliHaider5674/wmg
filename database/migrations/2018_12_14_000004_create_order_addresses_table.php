<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateOrderAddressesTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateOrderAddressesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'order_addresses';

    /**
     * Run the migrations.
     * @table order_addresses
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
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('zip', 45)->nullable();
            $table->string('country_code', 45);
            $table->string('phone', 45)->nullable();
            $table->string('email')->nullable();
            $table->string('latitude', 45)->nullable();
            $table->string('longitude', 45)->nullable();

            $table->index(["parent_id"], 'SHIPMENT_REQUEST_ADDRESS_SHIPMENT_REQUEST_FK_idx');
            $table->nullableTimestamps();


            $table->foreign('parent_id', 'SHIPMENT_REQUEST_ADDRESS_SHIPMENT_REQUEST_FK_idx')
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
