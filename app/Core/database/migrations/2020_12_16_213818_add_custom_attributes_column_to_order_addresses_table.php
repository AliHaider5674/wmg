<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddCustomAttributesColumnToOrderAddressesTable
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddCustomAttributesColumnToOrderAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('order_addresses', static function (Blueprint $table) {
            $table->json('custom_attributes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('order_addresses', static function (Blueprint $table) {
            $table->removeColumn('custom_attributes');
        });
    }
}
