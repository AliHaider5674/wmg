<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateImMulesoftShippingServiceMappersTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class CreateImMulesoftShippingServiceMappersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_mulesoft_shipping_service_mappers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('country_code', 3);
            $table->string('condition_name', 255)->default('package_weight');
            $table->text('delivery_type');
            $table->decimal('condition_from_value', 12);
            $table->decimal('condition_to_value', 12);
            $table->string('carrier_code', 50);
            $table->string('service_code', 50);
            $table->integer('dispatch_offset')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('im_mulesoft_shipping_service_mappers');
    }
}
