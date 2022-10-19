<?php
namespace App\Mom\Subscribers;

use App\Models\Validator\RegexRuleValidator;
use Illuminate\Support\Arr;
use App\OrderAction\Models\OrderAction;
use App\Core\Services\EventService;
use App\Models\Service\Model\ShipmentLineChange;
use WMGCore\Services\ConfigService;
use App\Mom\Constants\ConfigurationConstant;
use App\Mom\Constants\EventConstant;
use App\Models\Service\Event\MetaDataExtractor;
use App\OrderAction\Models\Services\OrderActionCreated;

/**
 * Handle warehouse events
 *
 * Class WarehouseEventSubscriber
 * @category WMG
 * @package  App\Mom\Subscribers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class WarehouseEventSubscriber
{
    private $configService;
    private $eventManager;
    private $metaDataExtractor;
    private $regexRuleValidator;
    public function __construct(
        ConfigService $configService,
        EventService $eventManager,
        MetaDataExtractor $metaDataExtractor,
        RegexRuleValidator $regexRuleValidator
    ) {
        $this->configService = $configService;
        $this->eventManager = $eventManager;
        $this->metaDataExtractor = $metaDataExtractor;
        $this->regexRuleValidator = $regexRuleValidator;
    }

    /**
     * Handle line change
     * @param ShipmentLineChange $lineChange
     * @return void
     * @throws \Exception
     */
    public function handle(ShipmentLineChange $lineChange)
    {
        $this->configService->load();
        $orderActionMap = $this->configService->getJson(ConfigurationConstant::REASON_CODE_ORDER_ACTION_MAP, []);

        $actions = [];
        foreach ($lineChange->items as $item) {
            if (array_key_exists($item->statusReason, $orderActionMap)) {
                $action = $orderActionMap[$item->statusReason];
                $rules = null;
                if (is_array($action)) {
                    $rules = $action['rules'];
                    $action = $action['action'];
                }
                if (!isset($actions[$action])) {
                    $actions[$action] = [
                        'rules' => $rules,
                        'items' => []
                    ];
                }
                $actions[$action]['items'][] = [
                    'sku' => $item->sku,
                    'reason_code' => $item->statusReason,
                ];
            }
        }
        $metaData = $this->metaDataExtractor->getMetaData($lineChange);
        $this->processActions($actions, $metaData);
    }

    /**
     * Process actions
     *
     * @param array $actions
     * @param array $metaData
     *
     * @return void
     */
    private function processActions(array $actions, array $metaData)
    {
        $orderId = Arr::get($metaData, 'order_id');
        $salesChannel = Arr::get($metaData, 'sales_channel', '*');

        if ($orderId === null) {
            return;
        }

        foreach ($actions as $action => $detail) {
            $rules = $detail['rules'];
            if (!$this->regexRuleValidator->isPassed($rules, $metaData)) {
                continue;
            }
            //Queue the calls
            $orderAction = OrderAction::where('action', $action)
                ->where('order_id', $orderId)
                ->where('sales_channel', $salesChannel)
                ->first();

            if ($orderAction !== null) {
                continue;
            }

            $orderAction = new OrderAction();
            $orderAction->fill([
                'action' => $action,
                'order_id' => $orderId,
                'sales_channel' => $salesChannel
            ]);
            $orderAction->save();
            $orderActionCreated = new OrderActionCreated();
            $orderActionCreated->fill($orderAction->toArray(), false);
            $orderActionCreated->setHiddenDetail($detail);
            $this->eventManager->dispatchEvent(
                EventConstant::EVENT_ORDER_ACTION_CREATED,
                $orderActionCreated
            );
        }
    }

    /**
     * Events that listen to
     *
     * @param $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            EventService::EVENT_PREFIX . '.' . EventService::EVENT_ITEM_WAREHOUSE_ACK,
            self::class . '@handle'
        );
    }
}
