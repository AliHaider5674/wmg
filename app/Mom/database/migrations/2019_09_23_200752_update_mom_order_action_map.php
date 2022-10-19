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
class UpdateMomOrderActionMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = Configuration::where('path', '=', MomConstant::REASON_CODE_ORDER_ACTION_MAP)->first();
        if ($config) {
            $config->fill([
                'path' => MomConstant::REASON_CODE_ORDER_ACTION_MAP,
                'value' => json_encode([
                    '2' => [
                        'rules' => [
                            'sales_channel' => '^(?!M113).*'
                        ],
                        'action' => OnHoldHandler::NAME
                    ],
                    'A' => [
                        'rules' => [
                            'sales_channel' => '^(?!M113).*'
                        ],
                        'action' => OnHoldHandler::NAME
                    ],
                    'B' => [
                        'rules' => [
                            'sales_channel' => '^(?!M113).*'
                        ],
                        'action' => OnHoldHandler::NAME
                    ]
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
        $config = Configuration::where('path', '=', MomConstant::REASON_CODE_ORDER_ACTION_MAP)->first();
        if ($config) {
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
    }
}
