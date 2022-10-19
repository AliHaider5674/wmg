<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateServiceEventCallsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateServiceEventCallsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'service_event_calls';

    /**
     * Run the migrations.
     * @table service_event_calls
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
            $table->integer('status')->default('0')
                ->comment('0 IS BEING DELIVERY, 1 DELIVERED, 2 ERROR');
            $table->integer('attempts')->default(0)
                ->nullable(false);
            $table->text('data')->nullable(true);

            $table->timestamps();

            $table->foreign('parent_id', 'FK_SERVICE_EVENT_CALL_SERVICE_EVENT_idx')
                ->references('id')->on('service_events')
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
