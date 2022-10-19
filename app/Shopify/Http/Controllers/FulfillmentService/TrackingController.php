<?php declare(strict_types=1);

namespace App\Shopify\Http\Controllers\FulfillmentService;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Core\Models\Shipment;
use Illuminate\Support\Facades\Log;

/**
 * Class TrackingController
 * @package App\Shopify\Http\Controllers
 */
class TrackingController extends Controller
{
    /**
     * Request parameter order number key
     */
    const PARAM_ORDER_NUMBER = 'order_names';

    const RESPONSE_MESSAGE_FOUND = 'Successfully received the tracking numbers';
    const RESPONSE_MESSAGE_NOT_FOUND = 'There are no tracking numbers for requested orders';
    const RESPONSE_STATUS_SUCCESS = 'true';
    const RESPONSE_STATUS_FAIL = 'false';

    /**
     * @var Shipment
     */
    private Shipment $shipment;

    /**
     * @param Shipment $shipment
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function __invoke(
        Request $request
    ): JsonResponse {
        //Follow https://shopify.dev/api/admin/rest/reference/shipping-and-fulfillment/fulfillmentservice
        //@todo add support of xml format

        $params = $request->all();
        $response = array();

        //Get tracking numbers for requested order names
        if (isset($params[self::PARAM_ORDER_NUMBER]) && !empty($params[self::PARAM_ORDER_NUMBER])) {
            Log::info(print_r($params[self::PARAM_ORDER_NUMBER], true));
            $trackingNumbers = $this->getTrackingNumbers($params[self::PARAM_ORDER_NUMBER]);
            $response = $this->formatTrackingNumberResponse($trackingNumbers);
        }
        return new JsonResponse(
            $response,
            200
        );
    }

    /**
     * getTrackingNumbers
     * @param $orders
     * @return
     */
    protected function getTrackingNumbers($requestedTrackingNumbers): array
    {
        $results = array();

        if (!is_array($requestedTrackingNumbers)) {
            $requestedTrackingNumbers[] = $requestedTrackingNumbers;
        }

        $trackingNumbers = $this->shipment::query()
            ->select(['shipments.tracking_number', 'orders.request_id'])
            ->join('orders', 'shipments.order_id', '=', 'orders.id')
            ->whereIn('orders.request_id', $requestedTrackingNumbers)
            ->get();

        foreach ($trackingNumbers as $trackNumber) {
            $results[$trackNumber->request_id] = $trackNumber->tracking_number;
        }

        return $results;
    }

    /**
     * formatTrackingNumberResponse
     * @param $trackingNumbers
     * @return array
     */
    private function formatTrackingNumberResponse($trackingNumbers): array
    {
        $response = array();
        $response['message'] = self::RESPONSE_MESSAGE_NOT_FOUND;

        if (!empty($trackingNumbers)) {
            $response['tracking_numbers'] = $trackingNumbers;
            $response['message'] = self::RESPONSE_MESSAGE_FOUND;
            $response['status'] = self::RESPONSE_STATUS_SUCCESS;
        }
        return $response;
    }
}
