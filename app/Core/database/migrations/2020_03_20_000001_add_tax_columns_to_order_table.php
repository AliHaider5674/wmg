<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\OrderItem;

/**
 * Add MES Source Map
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class AddTaxColumnsToOrderTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'orders';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->decimal('shipping_net_amount')
                ->nullable()
                ->default(0)
                ->after('vat_country');
            $table->decimal('shipping_gross_amount')
                ->nullable()
                ->default(0)
                ->after('shipping_net_amount');
            $table->decimal('shipping_tax_amount')
                ->nullable()
                ->default(0)
                ->after('shipping_gross_amount');
            $table->decimal('shipping_tax_rate')
                ->nullable()
                ->default(0)
                ->after('shipping_tax_amount');
            $table->text('shipping_tax_detail')
                ->nullable(true)
                ->default(null)
                ->after('shipping_tax_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('shipping_net_amount');
            $table->dropColumn('shipping_gross_amount');
            $table->dropColumn('shipping_tax_amount');
            $table->dropColumn('shipping_tax_rate');
            $table->dropColumn('shipping_tax_detail');
        });
    }
}
