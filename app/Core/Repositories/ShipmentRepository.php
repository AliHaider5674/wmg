<?php declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\Models\Shipment;
use WMGCore\Repositories\BaseRepository;
use App\Core\Repositories\ShipmentItemRepository;

/**
 * Class ShipmentRepository
 * @package App\Core\Repository
 * @SuppressWarnings(PHPMD)
 */
class ShipmentRepository extends BaseRepository
{
    private ShipmentItemRepository $shipmentItemRepository;
    public function __construct(
        Shipment $shipment,
        ShipmentItemRepository $shipmentItemRepository
    ) {
        parent::__construct($shipment);
        $this->shipmentItemRepository = $shipmentItemRepository;
    }

    public function createShipment(array $shipmentData) : Shipment
    {
        $items = $shipmentData['items'];
        unset($shipmentData['items']);
        /** @var Shipment $shipment */
        $shipment = $this->create($shipmentData);
        foreach ($items as $item) {
            $item['parent_id'] = $shipment->id;
            $this->shipmentItemRepository->create($item);
        }
        return $shipment;
    }
}
