<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class DeleteImMulesoftRedundantTables
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class DeleteImMulesoftRedundantTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('im_mulesoft_call_responses');
        Schema::dropIfExists('im_mulesoft_calls');
        Schema::dropIfExists('im_mulesoft_order_statuses');
        Schema::dropIfExists('im_mulesoft_stocks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
