<?php

use App\Core\Enums\OrderItemStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddStatusAndDropIdColumnsToOrderItemsTable
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AlterOrderItemsDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('order_line_id', 255)->change();
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
            $table->integer('order_line_id')->change();
        });
    }
}
