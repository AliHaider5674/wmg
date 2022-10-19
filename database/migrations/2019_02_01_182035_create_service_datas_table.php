<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create service data table
 *
 * Class CreateServiceDatasTable
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateServiceDatasTable extends Migration
{
    protected $tableName = 'service_datas';
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
            $table->unsignedInteger('parent_id')->nullable(false);
            $table->string('key')->unique()->nullable(false);
            $table->text('value')->nullable(false);
            $table->timestamps();

            $table->foreign('parent_id', 'SERVICE_DATA_SERVICE_FK_idx')
                ->references('id')->on('services')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
