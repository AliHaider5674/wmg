<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddPreorderColumnToProductTable
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddPreorderColumnToProductTable extends Migration
{
    const TABLE_NAME = 'products';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->timestamp('preorder')->nullable(true)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('preorder');
        });
    }
}
