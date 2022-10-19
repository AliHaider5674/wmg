<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateImMulesoftRequestsTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class CreateImMulesoftRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_mulesoft_requests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('0:RECEIVED", 1:PROCESSING, 3:COMPLETE, 4:ERROR')->index('status');
            $table->integer('attempts')->default(0);
            $table->string('message_id', 250)->index('message_id');
            $table->string('resource_type', 250)->index('resource_type');
            $table->longText('data')->nullable();
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
        Schema::dropIfExists('im_mulesoft_requests');
    }
}
