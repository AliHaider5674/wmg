<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateSFSRegistrationTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class CreateSFSRegistrationTable extends Migration
{
    const TABLE_NAME = 'shopify_fulfillment_service_registrations';
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
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('warehouse_id');
            $table->string('shopify_service_id', 255);
            $table->nullableTimestamps();

            $table->foreign('service_id', 'SHOPIFY_FULFILLMENT_REGISTRATION_SERVICE_IDX')
                ->references('id')
                ->on('services')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('warehouse_id', 'SHOPIFY_FULFILLMENT_REGISTRATION_WAREHOUSE_IDX')
                ->references('id')
                ->on('warehouses')
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
