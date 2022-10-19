<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateImMulesoftCallsTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class CreateImMulesoftCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_mulesoft_calls', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('1 is QUEUED, 2 is ERROR, 3 is PROCESSING, 4 is COMPLETE');
            $table->integer('attempts')->default(0);
            $table->longText('data')->nullable();
            $table->longText('order_ids')->nullable();
            $table->longText('order_item_ids')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('processed_at')->nullable();
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
        Schema::dropIfExists('im_mulesoft_calls');
    }
}
