<?php
use Illuminate\Database\Migrations\Migration;
use WMGCore\Configuration;
use App\Mom\Constants\ConfigurationConstant as MomConstant;
use App\Core\Constants\ConfigConstant as ConfigurationConstant;
use App\OrderAction\ActionHandlers\OnHoldHandler;

/**
 * Create alert events table
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AddInitialFulfillmentMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //MOM mapping
        $config = Configuration::where('path', '=', MomConstant::REASON_CODE_BACKORDER_MAP)->first();
        if (!$config) {
            $config = new Configuration();
            $config->fill([
                'path' => MomConstant::REASON_CODE_BACKORDER_MAP,
                'value' => json_encode(['3', '4', '5', 'M'])
            ]);
            $config->save();
        }

        $config = Configuration::where('path', '=', MomConstant::REASON_CODE_ORDER_STATUS_MAP)->first();
        if (!$config) {
            $config = new Configuration();
            $config->fill([
                'path' => MomConstant::REASON_CODE_ORDER_STATUS_MAP,
                'value' => json_encode([
                    '2' => 'PICKDECLINED',
                    '3' => 'PICKDECLINED',
                    '4' => 'PICKDECLINED',
                    '5' => 'PICKDECLINED',
                    'A' => 'PICKDECLINED',
                    'B' => 'PICKDECLINED',
                    'M' => 'PICKDECLINED',
                ])
            ]);
            $config->save();
        }

        $config = Configuration::where('path', '=', MomConstant::REASON_CODE_ORDER_ACTION_MAP)->first();
        if (!$config) {
            $config = new Configuration();
            $config->fill([
                'path' => MomConstant::REASON_CODE_ORDER_ACTION_MAP,
                'value' => json_encode([
                    '2' => OnHoldHandler::NAME,
                    'A' => OnHoldHandler::NAME,
                    'B' => OnHoldHandler::NAME
                ])
            ]);
            $config->save();
        }

        $config = Configuration::where('path', '=', MomConstant::REASON_CODE_TITLE_MAP)->first();
        if (!$config) {
            $config = new Configuration();
            $config->fill([
                'path' => MomConstant::REASON_CODE_TITLE_MAP,
                'value' => json_encode([
                    '0' => 'Pooling',
                    '1' => 'Presales',
                    '2' => 'Title deleted',
                    '3' => 'No stock/not available',
                    '4' => 'Normal BO',
                    '5' => 'Force to back order',
                    '6' => 'Delivery to far in the future',
                    'A' => 'Title unknown',
                    'B' => 'Trade restrictions',
                    'C' => 'No sales rights',
                    'S' => 'No stock & no BO allowed',
                    'D' => 'Blocked product',
                    'E' => 'Recall date set',
                    'F' => 'NO release date or in the future',
                    'M' => 'Not available',
                ])
            ]);
            $config->save();
        }

        $config = Configuration::where('path', '=', ConfigurationConstant::SHIPPING_METHOD_MAP)->first();
        if (!$config) {
            $config = new Configuration();
            $config->fill([
                'path' => ConfigurationConstant::SHIPPING_METHOD_MAP,
                'value' => json_encode([
                    'wmgFlatRateDomestic-StandardShipping' => '17',
                    'wmgFlatRateDomestic-ExpeditedShipping' => '16',
                    '17' => '17',
                    '16' => '16',
                    '*' => '17',
                ])
            ]);
            $config->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Configuration::whereIn('path', [
            MomConstant::REASON_CODE_BACKORDER_MAP,
            MomConstant::REASON_CODE_ORDER_STATUS_MAP,
            MomConstant::REASON_CODE_TITLE_MAP,
            ConfigurationConstant::SHIPPING_METHOD_MAP,
        ])->delete();
    }
}
