<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddAddressTypeToOrderOrderAddresses
 * @category WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class AddAddressTypeToOrderOrderAddresses extends Migration
{
    const ADDRESS_TYPE_DEFAULT = 'shipping';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->string('address_type', 8)
                ->default(self::ADDRESS_TYPE_DEFAULT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('order_addresses', 'address_type')) {
            Schema::table('order_addresses', function (Blueprint $table) {
                $table->dropColumn('address_type');
            });
        }
    }
}
