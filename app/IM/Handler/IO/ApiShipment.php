<?php


namespace App\IM\Handler\IO;

use App\Exceptions\NoRecordException;
use App\Models\AlertEvent;
use App\Models\ImShipments;
use App\Models\Service\ModelBuilder\Shipment\PackageParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\Models\Service\ModelBuilder\Shipment\ItemParameter as ShipmentItemParameter;
use Carbon\Carbon;

/**
 * Class ApiShipment
 * @category WMG
 * @package  App\IM\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ApiShipment extends ApiAbstract
{

    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    const ALERT_NAME = 'IM Shipment';
    const EXCEPTION_NO_RECORD_MESSAGE = 'No shipments';

    const API_FILTER_DATETIME_FORMAT = 'Y-m-d\TH:i:s';


    /**
     * Rest API uri
     * @var string
     */
    protected $apiURI = 'rest/v1/Shipment';

    /**
     * Api Name used for alert messages
     * @var string
     */
    protected $apiName = "Ingram Micro Shipment";

    /**
     * @var int
     */
    protected $shipmentReportId;

    /**
     * @inheritdoc
     *
     * @param array|null $data
     */
    public function start(array $data = null)
    {
        parent::start($data);
    }


    /**
     * applyDateFilter
     *
     */
    protected function applyDateFilter()
    {
        //query shipment table to find out the last time shipments were collected
        //query status = 1 and desc order by id

        $lastShipmentReport =
            ImShipments::where('status', self::STATUS_SUCCESS)
                ->orderBy('id', 'desc')
                ->first();

        //extract filter_end from db resultset and used this has api from datetime
        //Ensure to format from and to
        //from=2019-04-10T16:10:29&to=2019-04-02T17:10:29

        $filterFrom = now()->subDay();

        //collection shipment since last run
        $filterFrom = Carbon::now();
        if (!empty($lastShipmentReport)) {
            $filterFrom = Carbon::parse($lastShipmentReport->filter_to);
        }

        //set end datetime
        $filterTo = Carbon::now();

        $filter = sprintf(
            "from=%s&to=%s",
            $filterFrom->format(self::API_FILTER_DATETIME_FORMAT),
            $filterTo->format(self::API_FILTER_DATETIME_FORMAT)
        );

        //add to filters
        $this->addApiFilter($filter);

        $this->createShipmentRecord($filterFrom, $filterTo);
    }

    /**
     * Create IM shipment record
     *
     * @param $filterFrom
     * @param $filterTo
     */
    private function createShipmentRecord($filterFrom, $filterTo)
    {
        $shipmentReport = ImShipments::create([
            'filter_from' => $filterFrom,
            'filter_to' => $filterTo,
        ]);

        $this->shipmentReportId = $shipmentReport->id;
    }

    /**
     * Save IM shipment record
     *
     * @param $numberOfShipments
     * @param $status
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function saveShipmentRecord($numberOfShipments, $status = true, $messages = '')
    {
        if (empty($this->shipmentReportId)) {
            $this->createShipmentRecord(now(), now());
        }

        $shipmentReport = ImShipments::find($this->shipmentReportId);
        $shipmentReport->count = $numberOfShipments;
        $shipmentReport->status = $status;

        if (!empty($messages)) {
            $shipmentReport->messages = $messages;
        }

        $shipmentReport->save();
    }


    /**
     * @inheritdoc
     *
     * @param $callback
     */
    public function receive($callback)
    {
        //apply api filter
        //only collect shipments from the last time we called the API successfully
      // $this->applyDateFilter();

        //Get shipment from IM Shipment API
        $apiResponse = $this->getDataFromWarehouse();

        $numberOfShipments = count($apiResponse['Shipment']);

        $messages = '';
        if (!empty($apiResponse['Messages'])) {
            $messages = json_encode($apiResponse['Messages']);
        }

        if (!$apiResponse['HasSucceeded']) {
            $this->saveShipmentRecord($numberOfShipments, self::STATUS_FAILED, $messages);
            return;
        }

        $this->saveShipmentRecord($numberOfShipments, self::STATUS_SUCCESS, $messages);

        if ($numberOfShipments) {
            $this->handleShipments($apiResponse['Shipment'], $callback);
        }
    }

    /**
     * handleShipments
     *
     * @param $shipments
     * @param $callback
     */
    private function handleShipments($shipments, $callback)
    {
        //for each shipment create a new shipping Parameter object and send to callback
        foreach ($shipments as $shipment) {
            $shipmentParameter = new ShipmentParameter();

            //Use Magento order reference, as this is the only order number
            //returned by IM API.
            //TODO send and receive Fulfillment order id, via IM ExternalReference field
            $shipmentParameter->orderId = $shipment['OrderReference'];

            //create dummy package, as API currently doesnt send this info
            $package = new PackageParameter();
            $package->packageId = md5($shipment['OrderReference'] . uniqid());
            $package->carrier = $shipment['ShipmentMethod'];
            $package->trackingNumber = $shipment['TrackingNumber'];
            $shipmentParameter->addPackage($package);

            foreach ($shipment['ShipmentLines'] as $shippingLine) {
                $packageItem = new ShipmentItemParameter();

                //LineNumber == Fulfillment order_items.id
                $packageItem->orderItemId = $shippingLine['LineNumber'];
                $packageItem->quantity = $shippingLine['QuantityShipped'];
                $packageItem->sku = $shippingLine['SKU'];
                $shipmentParameter->addItemToPackage($packageItem, $package->packageId);
            }

            call_user_func($callback, $shipmentParameter);
        }
    }

    public function send($data, $callback = null)
    {
        // TODO: Implement send() method.
    }

    public function finish(array $data = null)
    {
        // TODO: Implement finish() method.
    }

    public function rollback(...$args)
    {
        // TODO: Implement rollback() method.
    }
}
