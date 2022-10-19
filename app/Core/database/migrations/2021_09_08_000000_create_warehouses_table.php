<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Core\Enums\WarehouseStatus;

/**
 * Class CreateCountryRegionsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateWarehousesTable extends Migration
{
    const TABLE_NAME = 'warehouses';
    /**
     * Run the migrations.
     * @table regions
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            return;
        }
        DB::transaction(function () {
            Schema::create(self::TABLE_NAME, function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('code', 45);
                $table->string('name', 255);
                $table->string('region', 45)->default('US');
                $table->tinyInteger('status')->default(1);
                $table->nullableTimestamps();
            });
            $warehouses = [
                [
                    'code' => 'GNAR',
                    'name' => 'Gnarlywood',
                    'region' => 'US',
                    'status' => WarehouseStatus::ACTIVE,
                ],
                [
                    'code' => 'US',
                    'name' => 'Legacy',
                    'region' => 'US',
                    'status' => WarehouseStatus::ACTIVE,
                ],
                [
                    'code' => 'IM',
                    'name' => 'Ingram Micro',
                    'region' => 'EU',
                    'status' => WarehouseStatus::ACTIVE,
                ],
                [
                    'code' => 'PF',
                    'name' => 'Printful',
                    'region' => 'US',
                    'status' => WarehouseStatus::ACTIVE,
                ],

            ];
            $timestamp = date("Y-m-d H:i:s");
            foreach ($warehouses as $warehouse) {
                $warehouse['created_at'] = $timestamp;
                $warehouse['updated_at'] = $timestamp;
                DB::table(self::TABLE_NAME)->insert($warehouse);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
