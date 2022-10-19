<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Class AddPrintfulCarrierConfigToConfigurationsTable
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddPrintfulCarrierConfigToConfigurationsTable extends Migration
{
    /**
     * JSON for shipping map
     */
    private const SHIPPING_MAP = [
        [
            "exp"=>"^UPS",
            "carrier"=>"ups"
        ],
        [
            "exp" => "^FEDEX",
            "carrier" => "fedex"
        ],
        [
            "exp" => "^USPS",
            "carrier" => "usps"
        ]
    ];

    /**
     * Config path
     */
    private const CONFIG_PATH = 'printful.carrier.map';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        DB::table('configurations')->insert([
            'path' => self::CONFIG_PATH,
            'value' => json_encode(self::SHIPPING_MAP),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('configurations')->where('path', self::CONFIG_PATH)
            ->delete();
    }
}
