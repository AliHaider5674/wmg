<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateImMulesoftShippingCarrierServicesTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class CreateImMulesoftShippingCarrierServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_mulesoft_shipping_carrier_services', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('carrier_code', 50);
            $table->text('carrier_name');
            $table->string('service_code', 50);
            $table->text('service_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('im_mulesoft_shipping_carrier_services');
    }
}
