<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateImMulesoftStock
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class CreateImMulesoftStocksTable extends Migration
{
    private string $tableName = 'im_mulesoft_stocks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('1 is QUEUED, 2 is ERROR, 3 is PROCESSING, 4 is COMPLETE');
            $table->integer('attempts')->default(0);
            $table->longText('data')->nullable();
            $table->integer('current_batch')
                ->default(0);
            $table->integer('total_batches')
                ->default(0);
            $table->text('messages')->nullable();
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
        Schema::dropIfExists($this->tableName);
    }
}
