<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add PR to region
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddPrToRegionTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'country_regions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $timestamp = date("Y-m-d H:i:s");
        DB::table($this->tableName)->updateOrInsert([
            'code' => 'PR',
            'name' => 'Puerto Rico',
            'country_code' => 'US',
            'country_name' => 'United States',
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table($this->tableName)->where('code', '=', 'PR')->delete();
    }
}
