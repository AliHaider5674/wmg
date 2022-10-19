<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create alert events table
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateOrderActionsTable extends Migration
{

    private $tableName = 'order_actions';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('order_id');
            $table->string('sales_channel')->default('*');
            $table->string('action')->comment('action handler');
            $table->text('setting')->nullable();
            $table->text('exec_data')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'sales_channel'], 'ORDER_ACTION_UNIQUE_ORDER');
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
