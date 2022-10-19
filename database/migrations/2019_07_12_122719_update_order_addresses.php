<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 *
 * Class UpdateOrderAddresses
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class UpdateOrderAddresses extends Migration
{
    const CUSTOMER_ADDRESS_TYPE_DEFAULT = 'shipping';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->string('customer_address_type', 8)
                ->default(self::CUSTOMER_ADDRESS_TYPE_DEFAULT);
        });
        if (Schema::hasColumn('order_addresses', 'address_type')) {
            Schema::table('order_addresses', function (Blueprint $table) {
                $table->dropColumn('address_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('order_addresses', 'customer_address_type')) {
            Schema::table('order_addresses', function (Blueprint $table) {
                $table->dropColumn('customer_address_type');
            });
        }
    }
}
