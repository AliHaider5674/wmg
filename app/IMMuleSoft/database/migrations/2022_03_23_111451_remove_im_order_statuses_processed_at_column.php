<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class RemoveImOrderStatusesProcessedAtColumn
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class RemoveImOrderStatusesProcessedAtColumn extends Migration
{
    private string $tableName = 'im_mulesoft_order_statuses';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (Schema::hasColumn($this->tableName, 'processed_at')) {
                $table->dropColumn('processed_at');
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
            if (!Schema::hasColumn($this->tableName, 'processed_at')) {
                $table->addColumn('timestamp', 'processed_at')->nullable();
            }
        });
    }
}
