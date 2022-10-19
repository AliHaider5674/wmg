<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddMessagesToImShipmentsTable
 * Add message field for IM_shipment table to log any error messages from API call
 *
 * @category WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class AddMessagesToImShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('im_shipments', function (Blueprint $table) {
            $table->text('messages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('im_shipments', function (Blueprint $table) {
            $table->dropColumn('messages');
        });
    }
}
