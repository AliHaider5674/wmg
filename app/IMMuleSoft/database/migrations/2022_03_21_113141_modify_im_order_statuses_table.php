<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class ModifyImOrderStatusesTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ModifyImOrderStatusesTable extends Migration
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
            if (Schema::hasColumn($this->tableName, 'status')) {
                $table->smallInteger('status')
                    ->comment('0:RECEIVED,1:PROCESSING,2:COMPLETE,3:ERROR')
                    ->change();
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
            if (Schema::hasColumn($this->tableName, 'status')) {
                $table->unsignedTinyInteger('status')
                    ->default(0)
                    ->comment('1 is QUEUED, 2 is ERROR, 3 is PROCESSING, 4 is COMPLETE')
                    ->change();
            }
        });
    }
}
