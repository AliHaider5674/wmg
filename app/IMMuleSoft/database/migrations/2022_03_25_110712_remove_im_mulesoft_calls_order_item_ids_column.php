<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class RemoveImMulesoftCallsOrderItemIdsColumn
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class RemoveImMulesoftCallsOrderItemIdsColumn extends Migration
{
    private string $tableName = 'im_mulesoft_calls';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (Schema::hasColumn($this->tableName, 'order_item_ids')) {
                $table->dropColumn('order_item_ids');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'order_item_ids')) {
                $table->addColumn('longText', 'order_item_ids')->nullable();
            }
        });
    }
}
