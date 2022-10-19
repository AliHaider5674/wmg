<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Threads Table
 *
 * Class CreateThreadsTable
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateSchedulesTable extends Migration
{
    protected $tableName = 'schedules';
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
            $table->string('name')->nullable(false);
            $table->integer('estimate_runtime')->nullable(false)
                ->comment('Estimate runtime to finish the job');
            $table->timestamp('started_at')->nullable(false);
            $table->timestamp('finished_at')->nullable(true);
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
