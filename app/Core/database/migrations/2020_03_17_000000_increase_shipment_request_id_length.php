<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Increase request_id length to 225
 *
 * Class IncreaseShipmentRequestIdLength
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class IncreaseShipmentRequestIdLength extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'orders';
    public $dataColumn = 'request_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string($this->dataColumn, 225)->nullable()->change();
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
            $table->string($this->dataColumn, 45)->nullable()->change();
        });
    }
}
