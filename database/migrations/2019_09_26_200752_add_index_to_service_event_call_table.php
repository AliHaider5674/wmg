<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Configuration;
use App\Console\Commands\Alert;

/**
 * Add index for created at column
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AddIndexToServiceEventCallTable extends Migration
{
    const INDEX_NAME = 'IDX_SERVICE_EVENT_CALL_CREATED_AT';
    private $tableName = 'service_event_calls';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->index(['created_at'], self::INDEX_NAME);
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
            $table->dropIndex(self::INDEX_NAME);
        });
    }
}
