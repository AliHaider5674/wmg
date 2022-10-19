<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateServiceEventsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateServiceEventsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'service_events';

    /**
     * Run the migrations.
     * @table service_events
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tableName)) {
            return;
        }
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('parent_id');
            $table->integer('status');
            $table->string('event');
            $table->timestamps();

            $table->foreign('parent_id', 'SERVICE_EVENT_SERVICE_FK_idx')
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
