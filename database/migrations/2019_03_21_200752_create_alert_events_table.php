<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WMGCore\Configuration;
use App\Console\Commands\Alert;

/**
 * Create alert events table
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateAlertEventsTable extends Migration
{
    private $tableName = 'alert_events';
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
            $table->string('name', 45);
            $table->string('type', 45);
            $table->string('level', 45);
            $table->text('content');
            $table->timestamps();
        });

        //SETUP INIT DATA
        $config = Configuration::where('path', '=', Alert::CONFIG_PATH_ALERT_SEND_TO)->first();
        if (!$config) {
            $config = new Configuration();
            $config->fill([
                'path' => Alert::CONFIG_PATH_ALERT_SEND_TO,
                'value' => 'darren.chen@wmg.com,maryGrace.rohrs@wmg.com'
            ]);
            $config->save();
        }
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
