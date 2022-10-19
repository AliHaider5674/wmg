<?php
namespace App\Mom\Models\Service\Event\ClientHandler;

use Illuminate\Support\Arr;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use MomApi\Client;
use App\Mom\Constants\EventConstant;
use App\Mom\Constants\ConfigurationConstant;

/**
 * Default MOM request handler
 *
 * Class DefaultHandler
 * @category WMG
 * @package  App\Mom\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderActionCreatedHandler extends DefaultHandler
{
    protected $handEvents = [
        EventConstant::EVENT_ORDER_ACTION_CREATED
    ];

    /**
     * Handle all requests
     * @param string $eventName
     * @param \App\Models\Service\Event\RequestData\RequestDataInterface $request
     * @param Client                                                     $client
     *
     * @return mixed
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        $addCommentEvent = $this->eventMap->getMomEvent(EventConstant::EVENT_ORDER_ACTION_CREATED);
        /** @var \App\OrderAction\Models\Services\OrderActionCreated $data */
        $data = $request->getData();
        $detail = $data->getHiddenDetail();
        $skuMessage = $this->getSkuMessage($detail);
        $requestBody = [
            'order_comment' => [
                'order_id' => $data->orderId,
                'sales_channel_id' => $data->salesChannel,
                'created_date' => date('Y-m-d\TH:i:sP'),
                'user' => 'Fulfillment',
                'comment' => sprintf('Received %s, put order to "%s" in Fulfillment.', $skuMessage, $data->action)
            ]
        ];
        return $client->publish($addCommentEvent, $requestBody, '*');
    }

    private function getSkuMessage($detail)
    {
        $reasonCodeTitleMap = $this->configService->getJson(ConfigurationConstant::REASON_CODE_TITLE_MAP, []);
        $skuMessage = '';
        foreach ($detail['items'] as $item) {
            if (!empty($skuMessage)) {
                $skuMessage .= ',';
            }
            $reasonCodeTitle = Arr::get($reasonCodeTitleMap, $item['reason_code'], 'Unknown');
            $skuMessage .= sprintf('%s(%s-%s)', $item['sku'], $item['reason_code'], $reasonCodeTitle);
        }
        return $skuMessage;
    }
}
