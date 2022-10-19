<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create fail parameter table
 *
 * Class CreateFailedParametersTable
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateFailedParametersTable extends Migration
{

    public $tableName = 'failed_parameters';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tableName)) {
            return;
        }
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', [
                'ack',
                'shipment',
                'order',
                'stock'
            ]);
            $table->integer('attempts')
                ->default(0);
            $table->string('last_error');
            $table->text('data')->default(null)->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
