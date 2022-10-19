<?php
namespace App\Mom\MomEventSubscribers;

use Illuminate\Support\Facades\Log;
use MomApi\Topic\SubscriberInterface;
use App\Models\Request\OrderProcessor;

/**
 * A subscriber that handle shipment request from MOM
 *
 * Class RequestShipment
 * @category WMG
 * @package  App\Mom\Subscribers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class RequestShipment implements SubscriberInterface
{
    protected $orderProcessor;
    private $logger;
    public function __construct(OrderProcessor $orderProcessor, Log $logger)
    {
        $this->orderProcessor = $orderProcessor;
        $this->logger = $logger;
    }

    /**
     * Handle subscription
     * @param array $data
     * @return mixed|void
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $orderId = isset($data['order_id']) ? $data['order_id'] : null;
            $this->logger::info('Received order '. $orderId);
            $this->orderProcessor->save($data);
        } catch (\Exception $e) {
            $this->logger::critical($orderId . ' is processed with error. '. $e->getMessage());
            throw $e;
        }
    }
}
