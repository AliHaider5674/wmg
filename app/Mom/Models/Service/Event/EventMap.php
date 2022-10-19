<?php
namespace App\Mom\Models\Service\Event;

use App\Core\Services\EventService;
use App\Mom\Constants\EventConstant;
use Exception;

/**
 * Event maps between internal and MOM
 *
 * Class EventMap
 * @category WMG
 * @package  App\Mom\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class EventMap
{
    //LEFT IS THE INTERNAL EVENT, RIGHT IS MOM EVENT
    protected $events = [
        EventService::EVENT_ITEM_SHIPPED =>
            'magento.logistics.warehouse_management.lines_shipped',
        EventService::EVENT_ITEM_WAREHOUSE_ACK =>
            'magento.logistics.shipment_request_management.lines_change_status',
        EventService::EVENT_SOURCE_UPDATE =>
            'magento.inventory.source_stock_management.update',
        EventConstant::EVENT_ORDER_ACTION_CREATED=> 'magento.sales.order_management.create_comment'
    ];

    public function getMomEvent($internalEvent)
    {
        if (!isset($this->events[$internalEvent])) {
            throw new Exception('There are no MOM event for '. $internalEvent);
        }
        return $this->events[$internalEvent];
    }
}
