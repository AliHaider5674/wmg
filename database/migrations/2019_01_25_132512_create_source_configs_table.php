<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * CreateSourceConfigsTable
 *
 * @category WMG
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class CreateSourceConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_configs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('distribution_id', 255);
            $table->string('source_id', 255);
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
        Schema::dropIfExists('source_configs');
    }
}
