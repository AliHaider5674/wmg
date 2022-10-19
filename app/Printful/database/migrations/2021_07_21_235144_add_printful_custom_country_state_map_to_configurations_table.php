<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Class AddPrintfulCustomCountryStateMapToConfigurationsTable
 *
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddPrintfulCustomCountryStateMapToConfigurationsTable extends Migration
{

    /**
     * Config path
     */
    private const CONFIG_PATH = 'printful.custom.country.state.map';

    private const COUNTRY_STATE_MAP = [
        'US' => [
            'states' => [
                ['code' => 'AA', 'name' => 'Armed Forces Americas'],
                ['code' => 'AC', 'name' => 'Armed Forces Canada'],
                ['code' => 'AE', 'name' => 'Armed Forces Europe'],
                ['code' => 'AM', 'name' => 'Armed Forces Middle East'],
                ['code' => 'AP', 'name' => 'Armed Forces Pacific']
            ]
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        DB::table('configurations')->insert([
            'path' => self::CONFIG_PATH,
            'value' => json_encode(self::COUNTRY_STATE_MAP),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('configurations')->where('path', self::CONFIG_PATH)
            ->delete();
    }
}
