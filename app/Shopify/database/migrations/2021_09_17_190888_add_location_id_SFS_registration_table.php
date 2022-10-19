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
class AddLocationIdSFSRegistrationTable extends Migration
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
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('shopify_location_id', 255)->nullable(true)->after('shopify_service_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('shopify_location_id');
        });
    }
}
