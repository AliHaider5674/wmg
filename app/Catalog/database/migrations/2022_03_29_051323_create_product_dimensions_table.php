<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateProductDimensionsTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class CreateProductDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_dimensions', function (Blueprint $table) {

            $table->id();
            $table->string('product_sku')->nullable(false);
            $table->foreign('product_sku', 'PRODUCT_DIMENSION_PRODUCT_IDX')
                ->references('sku')
                ->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');


            $table->string('type');
            $table->string('unit');
            $table->float('value', 12, 4)->default(0);
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
        Schema::dropIfExists('product_dimensions');
    }
}
