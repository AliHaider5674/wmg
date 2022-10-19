<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * CreateMesStocksTable
 *
 * @category WMG
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class CreateMesStocksTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'mes_stocks';

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
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('file')->nullable(false);
            $table->integer('source_count')
                ->nullable(false)
                ->default(0);
            $table->integer('sku_count')
                ->nullable(false)
                ->default(0);
            $table->integer('status')
                ->default('0')
                ->comment('0 IS PROCESSING, 1 IS PROCESSED, 2 IS ERROR');
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
