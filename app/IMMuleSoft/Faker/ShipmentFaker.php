<?php

namespace App\IMMuleSoft\Faker;

use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Models\ImMulesoftRequest;
use App\IMMuleSoft\Repositories\ImMulesoftRequestRepository;
use Exception;

/**
 * Class ShipmentFaker
 * @package App\IMMuleSoft\Faker
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ShipmentFaker
{
    private ShipmentMap $shipmentMap;
    private ImMulesoftRequestRepository $requestRepository;

    /**
     * @param ShipmentMap $shipmentMap
     * @param ImMulesoftRequestRepository $requestRepository
     */
    public function __construct(
        ShipmentMap $shipmentMap,
        ImMulesoftRequestRepository $requestRepository
    ) {
        $this->shipmentMap = $shipmentMap;
        $this->requestRepository = $requestRepository;
    }

    /**
     * fake
     * @param $orders
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function fake($orders, array $options): array
    {
        $orderStatuses = array();
        $result = array();

        //iterate through $orders
        foreach ($orders as $order) {
            //build order status payload
            $orderStatuses[] = $this->shipmentMap->handle($order, $options);
        }

        if (!empty($orderStatuses)) {
            $requestData = json_encode($orderStatuses);

            /**
             * @Todo move to trait
             */
            $messageId = hash('sha1', $requestData);

            /**
             * @Todo move to trait
             */
            if ($this->requestRepository
                ->isUnique(
                    $messageId,
                    ResourceConstant::RESOURCE_TYPE_SALES_ORDER_STATUS
                )) {
                $this->requestRepository->create(
                    [
                        'status' => ImMulesoftRequest::STATUS_RECEIVED,
                        'data' => $requestData,
                        'additional' => '',
                        'message_id' => $messageId,
                        'resource_type' => ResourceConstant::RESOURCE_TYPE_SALES_ORDER_STATUS
                    ]
                );
            }
        }

        $result['count'] = count($orderStatuses);
        return $result;
    }
}
