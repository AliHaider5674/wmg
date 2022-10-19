<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddQuantityReturnedColumnToOrderItemsTable
 * add return quantity to table
 * @SuppressWarnings(PHPMD.LongClassName);
 */
class AddQuantityReturnedColumnToOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity_returned', 12, 4)->default(0)
                ->after('quantity_backordered');
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
            $table->dropColumn('quantity_returned');
        });
    }
}
