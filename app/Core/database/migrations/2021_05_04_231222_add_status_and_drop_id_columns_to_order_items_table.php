<?php

use App\Core\Enums\OrderItemStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddStatusAndDropIdColumnsToOrderItemsTable
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddStatusAndDropIdColumnsToOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->tinyInteger('drop_status')->after('order_line_number')->default(OrderItemStatus::RECEIVED);
            $table->unsignedInteger('drop_id')->after('order_line_number')->nullable();

            $table->foreign('drop_id', 'ORDER_ITEM_ORDER_DROPS_FK_idx')
                ->references('id')->on('order_drops')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('ORDER_ITEM_ORDER_DROPS_FK_idx');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('drop_id');
            $table->dropColumn('drop_status');
        });
    }
}
