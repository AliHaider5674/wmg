<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class RemoveImMulesoftCallsQueuedAtColumn
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class RemoveImMulesoftCallsQueuedAtColumn extends Migration
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
            if (Schema::hasColumn($this->tableName, 'queued_at')) {
                $table->dropColumn('queued_at');
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
            if (!Schema::hasColumn($this->tableName, 'queued_at')) {
                $table->addColumn('timestamp', 'queued_at')->nullable();
            }
        });
    }
}
