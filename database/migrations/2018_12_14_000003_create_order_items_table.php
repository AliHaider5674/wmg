<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\OrderItem;

/**
 * Class CreateOrderItemsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateOrderItemsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'order_items';

    /**
     * Run the migrations.
     * @table order_items
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
            $table->unsignedInteger('parent_id');
            $table->integer('order_line_id')
                ->nullable(false)
                ->comment('EXTERNAL ORDER LINE ID');
            $table->integer('order_line_number')
                ->nullable(false)
                ->comment('ORDER LINE NUMBER');
            $table->string('sku', 45);
            $table->string('name');
            $table->string('source_id', 45)->nullable(false);
            $table->unsignedInteger('aggregated_line_id');
            $table->decimal('net_amount', 12, 4)->nullable();
            $table->decimal('gross_amount', 12, 4)->nullable();
            $table->decimal('tax_amount', 12, 4)->nullable();
            $table->decimal('tax_rate', 12, 4)->nullable();
            $table->string('currency', 45);
            $table->string('item_type', 45)
                ->nullable(false)
                ->default(OrderItem::PRODUCT_TYPE_PHYSICAL);
            $table->unsignedInteger('parent_order_line_number')->nullable();
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('quantity_shipped', 12, 4)->default(0);
            $table->decimal('quantity_backordered', 12, 4)->default(0);
            $table->text('custom_attributes')->nullable(true);
            $table->index(["parent_id"], 'SHIPMENT_REQUEST_ITEM_SHIPMENT_REQUEST_FK_idx');
            $table->timestamps();


            $table->foreign('parent_id', 'SHIPMENT_REQUEST_ITEM_SHIPMENT_REQUEST_FK_idx')
                ->references('id')->on('orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
