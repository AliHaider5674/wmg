<?php
namespace App\MES\Handler\IO;

use App\Core\Handlers\IO\IOInterface;
use App\Services\FileSystemService;
use Carbon\Carbon;
use FileDataConverter\File\Flat;
use App\Models\Order;
use App\Models\CountryRegion;
use App\Models\OrderItem;

/**
 * Save list of orders into a fix length flat file that MES understand
 * The process flow is start->send->finish
 *      start: prepare header data
 *      send: prepare order and order item data
 *      finish: output file based on the prepared data
 *
 * Class FlatOrder
 * @category WMG
 * @package  App\MES\Handler\IO
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FlatOrder implements IOInterface
{
    const RECORD_TYPE_HEADER = 'MSGHDR';
    const RECORD_TYPE_ORDER = 'ORDHDR';
    const RECORD_TYPE_ORDER_ITEM = 'ORDDTL';
    const RECORD_TYPE_FOOTER = 'MSGTRL';
    const HEADER_BATCH_RUN_ID_PREFIX = 'MAGENTO';
    const ORDER_BATCH_RUN_ID_PREFIX = 'EDI2ODS';
    const ORDER_ITEM_BATCH_RUN_ID_PREFIX = 'EDI2ODS';
    const NEW_RELEASE_FLAG = 'NREL';

    //Key Fields
    const ORDER_NUMBER_FIELD = 'customer_order_reference';         //Source order number
    const ORDER_ID_FIELD = 'customer_e_order_rf';                  //Internal order ID
    const ORDER_ITEM_NUMBER_FIELD = 'end_customer_order_line_ref'; //Source order item ID
    const ORDER_ITEM_ID_FIELD = 'customer_order_line_ref';         //Internal order item ID


    protected $fileIO;
    protected $fileName;
    protected $data;
    protected $tmpPath;
    protected $destPath;

    protected $orderIndexNumber;
    protected $contentLineCount;
    protected $commonFields;
    protected $remoteConnection;
    protected $localConnection;


    protected $fileSystemService;

    public function __construct(
        Flat $fileIO,
        array $config,
        FileSystemService $fileSystemService
    ) {
        $this->fileIO = $fileIO;
        $this->tmpPath = $config['tmp_dir'];
        $this->destPath = $config['live_dir'];
        $this->remoteConnection = $config['remote_connection'];
        $this->localConnection = $config['local_connection'];
        $this->fileSystemService = $fileSystemService;
    }

    /**
     * Start preparing MES data from order models
     * @param array|null $data
     * @return void
     */
    public function start(array $data = null)
    {
        $this->init();
        $now = new Carbon('UTC');
        /** @var \App\Models\OrderDrop $orderDropModel */
        $orderDropModel = $data['order_drop'];
        $this->fileName = 'WMGUS_' . $now->format('YmdHis') . '.txt';
        //Prepare header data;
        $this->commonFields = [
            'message_reference' => '',
            'message_source_code' => '',
            'message_type_code' => '',
            'date' => $now->format('Ymd'),
            'time' => $now->format('His'),
        ];
        $headerData = [
            'batch_run_identifier' => self::HEADER_BATCH_RUN_ID_PREFIX
                . $orderDropModel->getAttribute('id'),
            'record_type' => self::RECORD_TYPE_HEADER,
        ];

        $this->data[] = $this->mergeData($headerData, $this->commonFields);
        $orderDropModel->setAttribute('content', $this->fileName)->save();
    }


    /**
     * Parse order item to MES data
     * @param $data
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function send($data, $callback = null)
    {

        $this->parseOrder($data);
        $this->parseOrderItem($data);

        return $this;
    }

    /**
     * Parse order item to MES data without order items
     * @param array $data
     * @return $this
     */
    protected function parseOrder(array $data)
    {
        /** @var \App\Core\Models\RawData\Order $rawOrder */
        $rawOrder = $data[self::DATA_FIELD_RAW_ORDER];
        $rawShippingAddress = $rawOrder->shippingAddress;
        $orderAttributes = $rawOrder->customAttributes;

        $this->orderIndexNumber++;
        $this->contentLineCount++;
        /** @var \App\Models\OrderAddress $shippingAddress */
        $addressData = $rawShippingAddress->toArray(false);
        $stateCode = CountryRegion::getRegionCode($rawShippingAddress->state, $rawShippingAddress->countryCode);
        $addressData['state'] = $stateCode;
        $addressData['delivery_address_name_2'] = empty($rawShippingAddress->address2)
                                    ? null : $rawShippingAddress->address1;

        $addressData['address1'] = empty($rawShippingAddress->address2)
                                    ? $rawShippingAddress->address1 : $rawShippingAddress->address2;
        $addressData['delivery_address_name_3'] = $rawShippingAddress->phone;
        $orderData = $rawOrder->toArray(false);
        $orderLineData = [
            'record_type' => self::RECORD_TYPE_ORDER,
            self::ORDER_NUMBER_FIELD => $rawOrder->orderId,
            'order_index' => $this->orderIndexNumber,
            'batch_run_identifier' => self::ORDER_BATCH_RUN_ID_PREFIX . $this->orderIndexNumber,
            self::ORDER_ID_FIELD => $rawOrder->id,
            'delivery_special_instruction' => 'FT',
            'special_instruction_free_text' => $rawOrder->storeName,
            'shipment_mode' => $rawOrder->shippingMethod
        ];

        if (isset($orderAttributes['sell_to'])) {
            $orderLineData['jde_doc_no'] = $orderAttributes['sell_to'];
        }

        if (isset($orderAttributes['customer_service_email'])) {
            $orderLineData['internal_remark'] = $orderAttributes['customer_service_email'];
        }

        $customerEmail = isset($addressData['email']) ? $addressData['email'] : null;
        if ($customerEmail) {
            $orderLineData['delivery_note_remark'] = $customerEmail;
        }

        $preOrderDate = $this->getPreOrderDateOfOrder($data);
        if ($preOrderDate !== null) {
            $orderLineData['delivery_date'] = $preOrderDate->format('Ymd');
        }

        $orderLineData = $this->mergeData($orderLineData, $this->commonFields);
        $orderLineData = $this->mergeData($orderLineData, $orderData);
        $orderLineData = $this->mergeData($orderLineData, $addressData);
        $this->data[] = $orderLineData;
        return $this;
    }

    /**
     * Parse order items
     * @param array $data
     * @return $this
     */
    protected function parseOrderItem(array $data)
    {
        /** @var \App\Core\Models\RawData\OrderItem[] $orderItems */
        $orderItems = $data[self::DATA_FIELD_RAW_ORDER]->items;
        //MES MAX ORDER ITEM ID FIX
        $maxOrderItemId = pow(2, 31) - 1;
        $lineNumber = 0;
        foreach ($orderItems as $orderItem) {
            $lineNumber++;
            $this->contentLineCount++;
            $orderItemData = $orderItem->toArray(false);
            $orderItemData['unit_ppd'] = $this->formatPrice($orderItem->netAmount / $orderItem->quantity);
            $orderItemData['manual_price'] = $orderItemData['unit_ppd'];
            $orderLineId =  0;
            if (is_numeric($orderItem->orderLineId)) {
                $orderLineId =  $orderItem->orderLineId + 0 > $maxOrderItemId ? '' : $orderItem->orderLineId;
            }
            $orderItemLineData = [
                'record_type' => self::RECORD_TYPE_ORDER_ITEM,
                'batch_run_identifier' => self::ORDER_ITEM_BATCH_RUN_ID_PREFIX . $this->orderIndexNumber,
                'order_index' => $this->orderIndexNumber,
                'line_no' => $lineNumber,
                'order_quantity' => intval($orderItem->quantity),
                'delivery_quantity' => intval($orderItem->quantity),
                self::ORDER_ITEM_NUMBER_FIELD => $orderLineId,
                self::ORDER_ITEM_ID_FIELD => $orderItem->id,
                'barcode' => $orderItem->sku
            ];

            $customAttributes = $orderItem->customAttributes;

            //Add pre-order information
            if (isset($customAttributes['release_date'])) {
                $time = new Carbon($customAttributes['release_date'], 'UTC');
                $current = Carbon::now('UTC');
                if ($time->gt($current)) {
                    $orderItemData['delivery_special_instruction_1'] = self::NEW_RELEASE_FLAG;
                    //MES DOESN'T SUPPORT DELIVERY DATE AT ITEM LEVEL AT THE MOMENT
                    //$orderItemData['delivery_date'] = $time->format('Ymd');
                }
            }

            //cust order ref
            $orderItemLineData = $this->mergeData($orderItemLineData, $this->commonFields);
            $this->data[] = $this->mergeData($orderItemLineData, $orderItemData);
        }
        return $this;
    }

    /**
     * Output orders into MES flat file
     *
     * @param array|null $data
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function finish(array $data = null)
    {
        $footerData = [
            'record_type' => self::RECORD_TYPE_FOOTER,
            'number_of_lines' => $this->contentLineCount
        ];
        $this->data[] = $this->mergeData($footerData, $this->commonFields);
        $this->data[] = ["\n"];
        $this->outputFile();
        return $this;
    }

    /**
     * Output file
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function outputFile()
    {
        $tmpFile = $this->tmpPath . '/'. $this->fileName;
        $destFile = $this->destPath . '/'. $this->fileName;
        $tmpFullFile = $this->fileSystemService->useConnection($this->localConnection)
            ->getFullPath($tmpFile);
        $this->fileIO->write(
            $tmpFullFile,
            $this->data
        );

        if (!$this->fileSystemService->useConnection($this->remoteConnection)->exists($this->destPath)) {
            $this->fileSystemService
                ->useConnection($this->remoteConnection)
                ->makeDir($this->destPath);
        }

        $this->fileSystemService
            ->useConnection($this->remoteConnection)
            ->move($tmpFile, $destFile, $this->localConnection);
        return $this;
    }

    /**
     * This is not being use for output
     *
     * @param $callback
     * @return \Illuminate\Database\Eloquent\Model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function receive($callback)
    {
        return null;
    }

    /**
     * Rollback all changes
     * @param array|null $args
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function rollback(...$args)
    {
        $tmpFile = $this->tmpPath . '/'. $this->fileName;
        $destFile = $this->destPath . '/'. $this->fileName;

        if ($tmpFile
            && $this->fileSystemService->useConnection($this->localConnection)->exists($tmpFile)
        ) {
            $this->fileSystemService
                ->useConnection($this->localConnection)
                ->delete($tmpFile);
        }

        if ($destFile
            && $this->fileSystemService->useConnection($this->remoteConnection)->exists($destFile)
        ) {
            $this->fileSystemService
                ->useConnection($this->remoteConnection)
                ->delete($destFile);
        }
    }

    /**
     * Init data
     *
     * @return void
     */
    protected function init()
    {
        $this->data = [];
        $this->orderIndexNumber = 0;
        $this->contentLineCount = 0;
    }

    /**
     * Merge Data
     * @param array $data1
     * @param array $data2
     *
     * @return array
     */
    protected function mergeData(array $data1, array $data2)
    {
        return array_merge($data1, $data2);
    }

    protected function formatPrice($price)
    {
        return round($price * 100);
    }

    /**
     * Get preOrder date for order
     * @param array $data
     * @return null | Carbon
     */
    private function getPreOrderDateOfOrder(array $data)
    {
        /** @var OrderItem[] $orderItems */
        $orderItems = $data['items'];
        $preOrderDate = null;
        foreach ($orderItems as $orderItem) {
            $preOrderDateString = $orderItem->getCustomAttribute('release_date');
            if (!$preOrderDateString) {
                continue;
            }

            $current = new Carbon($preOrderDateString, 'UTC');
            if ($current->lte(Carbon::now('UTC'))) {
                continue;
            }

            if ($preOrderDate === null || $current->gt($preOrderDate)) {
                $preOrderDate = $current;
            }
        }
        return $preOrderDate;
    }
}
