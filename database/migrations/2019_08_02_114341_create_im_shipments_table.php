<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateImShipmentsTable
 * New table used to log shipment api calls
 * @category WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class CreateImShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_shipments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->dateTime('filter_from');
            $table->dateTime('filter_to');
            $table->integer('count')
                ->nullable(false)
                ->default(0);
            $table->integer('status')
                ->default('0')
                ->comment('1 IS SUCCESS, 2 IS ERROR');
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
        Schema::dropIfExists('im_shipments');
    }
}
