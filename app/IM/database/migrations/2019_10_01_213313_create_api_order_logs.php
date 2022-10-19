<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateApiOrderLogs
 * New table used to log errors for order api calls
 *
 * @category WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class CreateApiOrderLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_order_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id');
            $table->text('messages');
            $table->timestamps();

            //indexes
            $table->foreign('parent_id', 'order_table_id_fx_idx')
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
        Schema::dropIfExists('api_order_logs');
    }
}
