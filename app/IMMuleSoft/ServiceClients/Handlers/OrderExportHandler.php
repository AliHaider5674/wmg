<?php /** @noinspection ALL */

namespace App\IMMuleSoft\ServiceClients\Handlers;

use App\Exceptions\ServiceException;
use App\IMMuleSoft\Clients\IMMuleSoftSDK;
use App\IMMuleSoft\Constants\EventConstant;
use App\Models\AlertEvent;
use App\Models\OrderItem;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponseHandler
 * @package App\IMMuleSoft\ServiceClients\Handlers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderExportHandler extends HandlerAbstract
{
    use \App\IMMuleSoft\Models\Traits\Order;

    const API_URI = 'sales-orders';
    const RESPONSE_STATUS_CODE_SUCCESS = 0;
    const ALERT_NAME = 'Ceva:Orders';
    private array $successfulResponseCodes = [
        Response::HTTP_OK,
        Response::HTTP_ACCEPTED
    ];
    protected $handEvents = [
       EventConstant::EVENT_IMMULESOFT_ORDER_EXPORT
    ];

    const URI = 'responses';

    /**
     * handle
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param IMMuleSoftSDK $client
     * @return string
     * @throws GuzzleException
     */
    public function handle(string $eventName, RequestDataInterface $request, $client): string
    {
        if (!isset($request->getData()->orders) && empty($request->getData()->orders)) {
            throw new ServiceException(
                "missing order post data",
                ServiceException::NOT_ALLOW_RETRY
            );
        }

        if (empty($request->getData()->getHiddenOrderIds())) {
            throw new ServiceException(
                "missing hidden order ids data",
                ServiceException::NOT_ALLOW_RETRY
            );
        }

        try {
            $response = $client->post(self::API_URI, $request->getData()->orders);
            $responseMessage = $this->processResponse(
                $response,
                $request->getData()->getHiddenOrderIds(),
            );
        } catch (GuzzleException $guzzleException) {
            $this->processErrorResponse(
                $guzzleException,
                $request->getData()->getHiddenOrderIds()
            );

            throw new ServiceException(
                $guzzleException->getMessage(),
                ServiceException::ENDPOINT_ERROR
            );
        }

        return $responseMessage;
    }

    /**
     * processResponse
     * @param ResponseInterface $response
     * @param array $orderIds
     */
    protected function processResponse(
        ResponseInterface $response,
        array $orderIds
    ) : string {

        if (!in_array($response->getStatusCode(), $this->successfulResponseCodes)) {
            throw new ServiceException(
                "unexpected response code: " . $response->getStatusCode(),
                ServiceException::ENDPOINT_ERROR
            );
        }

        //Update order status to indicate that orders
        // have been queued at the fulfilment centre for processing
        if (Response::HTTP_ACCEPTED == $response->getStatusCode()) {
            $this->updateOrderStatus(
                $orderIds,
                \App\Core\Enums\OrderStatus::QUEUED_DROPPED,
                \App\Core\Enums\OrderItemStatus::QUEUED_DROPPED
            );
            return 'Orders have been queued at fulfilment centre';
        }

        //handle orders that have been processed successfully
        return $this->processSuccessfulOrders($response, $orderIds);
    }

    /**
     * getProcessedOrders
     * @param ResponseInterface $response
     * @param array $orderIds
     */
    private function processSuccessfulOrders(
        ResponseInterface $response,
        array $orderIds
    ) : string {
        //iterate through response
        $responseData = json_decode($response->getBody());

        $errorMessages = array();
        $erroredOrders = array();
        $successOrders = array();

        foreach ($responseData->responses as $orderResponse) {
            if (self::RESPONSE_STATUS_CODE_SUCCESS !== (int) $orderResponse->statusCode) {
                $errorMessages[] = sprintf(
                    'order:%s. message:%s',
                    $orderResponse->resourceCode,
                    $orderResponse->message
                );

                if (array_key_exists($orderResponse->resourceCode, $orderIds)) {
                    $erroredOrders[] = $orderIds[$orderResponse->resourceCode];
                }

                continue;
            }

            if (array_key_exists($orderResponse->resourceCode, $orderIds)) {
                $successOrders[] = $orderIds[$orderResponse->resourceCode];
            }
        }

        if (!empty($successOrders)) {
            $this->updateOrderStatus(
                $successOrders,
                \App\Models\Order::STATUS_DROPPED,
                \App\Core\Enums\OrderItemStatus::DROPPED
            );
        }

        if (!empty($erroredOrders)) {
            $this->updateOrderStatus(
                $erroredOrders,
                \App\Core\Enums\OrderStatus::SOFT_ERROR,
                \App\Core\Enums\OrderItemStatus::SOFT_ERROR
            );

            //alert
            $data = [
                'name' => self::ALERT_NAME,
                'content' => implode(PHP_EOL, $errorMessages),
                'type' => AlertEvent::TYPE_REQUEST_ERROR,
                'level' => AlertEvent::LEVEL_CRITICAL
            ];

            $this->raiseAlert($data);
        }

        return 'Orders have been processed';
    }

    /**
     * processErrorResponse
     * @param GuzzleException $guzzleException
     * @param array $orderIds
     */
    protected function processErrorResponse(
        GuzzleException $guzzleException,
        array $orderIds
    ) {
        //update order status with error for orders
        if (!empty($orderIds)) {
            $this->updateOrderStatus(
                $orderIds,
                \App\Models\Order::STATUS_ERROR,
                \App\Core\Enums\OrderItemStatus::ERROR
            );
        }

        //raise alert for errored orders
        $data = [
            'name' => self::ALERT_NAME,
            'content' => $guzzleException->getMessage(),
            'type' => AlertEvent::TYPE_CONNECTION_ERROR,
            'level' => AlertEvent::LEVEL_CRITICAL
        ];

        $this->raiseAlert($data);
    }

    protected function raiseAlert(array $data)
    {
        if (empty($data)) {
            return;
        }

        $alertEvent = new AlertEvent();
        $alertEvent->fill($data);
        $alertEvent->save();
    }
}
