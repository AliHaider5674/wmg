<?php

namespace App\IM\Handler;

use App\Core\Handlers\AbstractShipmentHandler;
use App\Exceptions\NoRecordException;
use App\IM\Handler\IO\ApiShipment;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use App\Models\AlertEvent;

/**
 * Class ApiShipmentHandler
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ShipmentHandler extends AbstractShipmentHandler
{
    /**
     * Warehouse handler name
     * @var string
     */
    protected $name = 'apiShipment';

    /**
     * configureShipmentBuilder
     */
    protected function configureShipmentBuilder()
    {
        $this->shipmentBuilder->setOrderIdField(ShipmentBuilder::FIELD_ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function handle() : void
    {
        $this->configureShipmentBuilder();
        $this->ioAdapter->start();

        try {
            $this->ioAdapter->receive(function ($parameter) {
                try {
                    $this->processShipmentParameter($parameter);
                } catch (\Exception $e) {
                    $failedParameter = $this->recordFailedParameter($parameter, $e->getMessage());
                    $this->recordProcessed($failedParameter);

                    return;
                }
            });
        } catch (NoRecordException $e) {
            $data = [
                'name' => ApiShipment::ALERT_NAME,
                'content' => $e->getMessage(),
                'type' => AlertEvent::TYPE_NO_RECORDS,
                'level' => AlertEvent::LEVEL_MEDIUM
            ];
            $alertEvent = new AlertEvent();
            $alertEvent->fill($data);
            $alertEvent->save();
        }
    }

    public function validate()
    {
        return true;
    }
}
