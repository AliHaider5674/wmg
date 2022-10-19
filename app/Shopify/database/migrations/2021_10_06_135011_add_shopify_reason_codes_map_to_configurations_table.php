<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Class AddShopifyReasonCodesMapToConfigurationsTable
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddShopifyReasonCodesMapToConfigurationsTable extends Migration
{

    private const CONFIG_PATH = 'shopify.reason_codes.map';
    private const TABLE_NAME = 'configurations';

    /**
     *  Default reason code configuration value
     */
    private const REASON_CODES_MAP = [
        '2' => ['order_status' => 'On Hold', 'reason' => 'Title Deleted'],
        '3' => ['order_status' => null, 'reason' => 'No Stock'],
        '4' => ['order_status' => null, 'reason' => 'The stock is out'],
        'A' => ['order_status' => 'Drop Error', 'reason' => 'Title Unknown'],
        'B' => ['order_status' => 'On Hold', 'reason' => 'Trade Restrictions'],
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

        DB::table(self::TABLE_NAME)->where('path', self::CONFIG_PATH)
            ->delete();

        DB::table(self::TABLE_NAME)->insert([
            'path' => self::CONFIG_PATH,
            'value' => json_encode(self::REASON_CODES_MAP),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table(self::TABLE_NAME)->where('path', self::CONFIG_PATH)
            ->delete();
    }
}
