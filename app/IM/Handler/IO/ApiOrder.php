<?php

namespace App\IM\Handler\IO;

use App\Core\Exceptions\Handler\IOException;
use App\Core\Handlers\IO\IOInterface;
use App\IM\Configurations\ImConfig;
use App\IM\Models\Order as IMOrder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use App\Models\AlertEvent;
use App\Core\Models\RawData\Order as RawOrder;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiOrder
 * @category WMG
 * @package  App\IM\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ApiOrder implements IOInterface
{

    const HTTP_OK = '200';
    /**
     * Order API uri
     */
    const API_ENDPOINT_URI = '/rest/v1/ORDERS/%s';

    const ALERT_NAME = 'IM Orders';

    /**
     * Order address types
     */
    const CUSTOMER_ADDRESS_TYPE_SHIPPING = 'shipping';
    const CUSTOMER_ADDRESS_TYPE_BILLING  = 'billing';



    const ORDER_STATUS = 'processing';
    const ORDER_LINE_STATUS = 'processing';

    /**
     * @var ImConfig
     */
    protected $config;

    protected $apiUri;

    /** @var Client */
    protected $apiClient;
    protected $orderIndexNumber;
    protected $contentLineCount;


    /**
     * ApiOrder constructor.
     *
     * @param ImConfig $config
     */
    public function __construct(ImConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @param array|null $data
     */
    public function start(array $data = null)
    {
        //setup API client
        $this->apiClient = new Client(['base_uri' => $this->config->getBaseApiEndpoint()]);
    }

    /**
     * @inheritdoc
     * @param $callback
     *
     * @return mixed
     */
    public function receive($callback)
    {
        return null;
    }

    /**
     * Send order
     * @param $data
     * @param null $callback
     *
     * @return void
     * @throws \App\Core\Exceptions\Handler\IOException
     */
    public function send($data, $callback = null)
    {
        if (!$this->isDataValid($data)) {
            return;
        }

        $rawOrder = $data[self::DATA_FIELD_RAW_ORDER];

        //build API order payload
        $payload = $this->buildApiOrderPayload($rawOrder);

        //submit to API
        $response = $this->sendOrder($payload);

        $this->handleApiResponse($response);
    }

    protected function isDataValid(array $data)
    {
        if (!is_array($data)) {
            return false;
        }

        if (!isset($data[self::DATA_FIELD_ORDER])) {
            return false;
        }

        if (!isset($data[self::DATA_FIELD_ORDER_ITEMS])) {
            return false;
        }

        return true;
    }


    /**
     * Build request payload
     *
     * @param \App\Core\Models\RawData\Order           $rawOrder
     *
     * @return \App\IM\Models\Order
     */
    protected function buildApiOrderPayload(RawOrder $rawOrder)
    {
        //build order payload
        $imOrder = new IMOrder();

        //set order information
        $this->setOrderInformation($imOrder, $rawOrder);

        //set shipping address
        $this->setShippingAddress($imOrder, $rawOrder);

        //set billing address
        $this->setBillingAddress($imOrder, $rawOrder);


        //shipping info
        $imOrder->setShippingCostGross($rawOrder->shippingGrossAmount);

        $this->setShippingMethod($imOrder, $rawOrder);


        //set order items
        $this->setOrderItems($imOrder, $rawOrder);


        return $imOrder;
    }

    /**
     * Set order information
     *
     * @param \App\IM\Models\Order $imOrder
     * @param \App\Core\Models\RawData\Order $rawOrder
     *
     * @return void
     */
    protected function setOrderInformation(IMOrder $imOrder, RawOrder $rawOrder)
    {
        //set order data
        $imOrder->setOrderReference($rawOrder->orderId);
        $imOrder->setOrderDate($rawOrder->createdAt);
        $imOrder->setOrderStatus(self::ORDER_STATUS);
    }

    /**
     * @param IMOrder $imOrder
     * @param RawOrder $rawOrder
     */
    protected function setShippingMethod(IMOrder $imOrder, RawOrder $rawOrder)
    {
        //Todo in the future we may decide to send each item via a different shipping method
        //therefore shipping method is at order item level.
        //For now we send shipping method at order level

        //Get shipping method from an order item
        foreach ($rawOrder->items as $orderItem) {
            $customAttributes = $orderItem->customAttributes;
            if (isset($customAttributes['shipping_carrier']) && !empty($customAttributes['shipping_carrier'])) {
                $imOrder->setShippingMethod($customAttributes['shipping_carrier']);
                break;
            }
        }
    }

    /**
     * @param \App\IM\Models\Order $imOrder
     * @param RawOrder             $rawOrder
     */
    protected function setShippingAddress(IMOrder $imOrder, RawOrder $rawOrder)
    {
        $address = $this->getAddressByType($rawOrder, self::CUSTOMER_ADDRESS_TYPE_SHIPPING);
        $imOrder->setShippingAddress($address);
    }

    /**
     * @param \App\IM\Models\Order $imOrder
     * @param RawOrder             $rawOrder
     */
    protected function setBillingAddress(IMOrder $imOrder, RawOrder $rawOrder)
    {
        $address = $this->getAddressByType($rawOrder, self::CUSTOMER_ADDRESS_TYPE_BILLING);
        $imOrder->setBillingAddress($address);
    }

    /**
     * Get Order's address - either shipping | billing
     * @param RawOrder $rawOrder
     * @param string   $addressType
     *
     * @return mixed
     */
    protected function getAddressByType(RawOrder $rawOrder, string $addressType)
    {
        if (self::CUSTOMER_ADDRESS_TYPE_SHIPPING == $addressType) {
            $address = $rawOrder->shippingAddress;
        }

        if (self::CUSTOMER_ADDRESS_TYPE_BILLING == $addressType) {
            $address = $rawOrder->billingAddress;
        }

        $row = [];
        $row['CompanyName'] = '';
        $row['FullName'] = $address->customerName;
        $row['FirstName'] =  $address->firstName;
        $row['LastName'] = $address->lastName;
        $row['HouseNumber'] = '';
        $row['AddressLine1'] = $address->address1;
        $row['AddressLine2'] = $address->address2;
        $row['City'] = $address->city;
        $row['PostalCode'] =  $address->zip;
        $row['Country_ISO2'] =  $address->countryCode;

        if (!empty($address->phone)) {
            $row['PhoneNumberDay'] = $address->phone;
        }

        if (!empty($address->email)) {
            $row['email'] = $address->email;
        }

        return $row;
    }


    /**
     * Send order to warehouse via API
     * @param \App\IM\Models\Order $imOrder
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendOrder(IMOrder $imOrder)
    {
        $apiUri = sprintf(self::API_ENDPOINT_URI, $imOrder->getOrderReference());
        $data = $imOrder->getAPIData();

        try {
            $response = $this->apiClient->put($apiUri, [
                'headers' => ['Content-type' => 'application/json'],
                'auth' => [
                    $this->config->getApiUsername(),
                    $this->config->getApiPassword()
                ],
                'json' => $data,
            ]);

            return $response;
        } catch (ConnectException $connectException) {
            //alert
            $data = [
                'name' => self::ALERT_NAME,
                'content' => $connectException->getMessage(),
                'type' => AlertEvent::TYPE_CONNECTION_ERROR,
                'level' => AlertEvent::LEVEL_CRITICAL
            ];
            $alertEvent = new AlertEvent();
            $alertEvent->fill($data);
            $alertEvent->save();
            throw $connectException;
        } catch (ClientException $clientException) {
            //alert
            $data = [
                'name' => self::ALERT_NAME,
                'content' => $clientException->getMessage(),
                'type' => AlertEvent::TYPE_CONNECTION_ERROR,
                'level' => AlertEvent::LEVEL_CRITICAL
            ];
            $alertEvent = new AlertEvent();
            $alertEvent->fill($data);
            $alertEvent->save();
            throw $clientException;
        }
    }


    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return $this
     * @throws \App\Core\Exceptions\Handler\IOException
     */
    protected function handleApiResponse(ResponseInterface $response)
    {
        $message = "Send Order Response status code is: " . $response->getStatusCode();
        if ($response->getStatusCode() == self::HTTP_OK) {
            $data =  \GuzzleHttp\json_decode(
                $response->getBody()
            );
            if (!$data->HasSucceeded) {
                if (!empty($data->Messages)) {
                    $message = json_encode($data->Messages);
                }
                throw new IOException(
                    $message,
                    IOException::API_SEND_ERROR
                );
            }
            return $this;
        }

        //alert
        $data = [
            'name' => self::ALERT_NAME,
            'content' => $message,
            'type' => AlertEvent::TYPE_CONNECTION_ERROR,
            'level' => AlertEvent::LEVEL_CRITICAL
        ];
        $alertEvent = new AlertEvent();
        $alertEvent->fill($data);
        $alertEvent->save();

        throw new IOException(
            $message,
            IOException::API_SEND_ERROR
        );
    }


    /**
     * setOrderItems
     *
     * @param \App\IM\Models\Order  $imOrder
     * @param RawOrder              $rawOrder
     */
    protected function setOrderItems(IMOrder $imOrder, RawOrder $rawOrder)
    {
        $lines = [];
        foreach ($rawOrder->items as $orderItem) {
            //For now using the Fulfillment order.items.id field to uniquely
            // identify order item
            $line = [];
            $line['LineNumber'] = $orderItem->id;
            $line['SKU'] = (string) $orderItem->sku ;
            $line['OrderLineStatus'] = self::ORDER_LINE_STATUS;
            $line['UnitPriceGross'] = $orderItem->grossAmount;
            $line['Quantity'] = (int) $orderItem->quantity;
            $line['Description'] = $orderItem->name;
            $lines[] = $line;
        }

        $imOrder->setOrderLines($lines);
    }


    /**
     * @param array|null $data
     *
     * @return mixed
     */
    public function finish(array $data = null)
    {
    }

    /**
     * @param array ...$args
     *
     * @return mixed
     */
    public function rollback(...$args)
    {
    }
}
