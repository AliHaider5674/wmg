<?php declare(strict_types=1);

namespace App\Printful\Mappers;

use App\Core\Models\RawData\Order;
use App\Core\Mappers\OrderProcessorInterface;
use App\Core\Mappers\ConditionallyRuns;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Printful\Service\PrintfulCountryService;
use Printful\Exceptions\PrintfulApiException;
use Printful\Exceptions\PrintfulException;

/**
 * Class StateCodeMapper
 * @package App\Printful\Mappers
 *
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class StateCodeMapper implements OrderProcessorInterface, ConditionallyRuns
{
    /**
     * @var PrintfulCountryService
     */
    private $countryService;

    /**
     * StateCodeMapper constructor.
     * @param PrintfulCountryService $countryService
     */
    public function __construct(
        PrintfulCountryService $countryService
    ) {
        $this->countryService = $countryService;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function shouldRun(Order $order): bool
    {
        //Check if Printful requires state_code for Country
        //Use Printful API to identify which countries require state_code
        $shippingAddress = $order->shippingAddress;
        return $this->countryService->isStateCodeRequired($shippingAddress->countryCode);
    }


    /**
     * @param Order $order
     * @return Order
     * @throws PrintfulApiException
     * @throws PrintfulException
     */
    public function processOrder(Order $order): Order
    {
        Log::info(__METHOD__);

        $shippingAddress = $order->shippingAddress;

        if (empty($shippingAddress->state)) {
            return $order;
        }

        if (!$this->shouldRun($order)) {
            return $order;
        }

        //get states by country
        $states = $this->countryService->getStateByCountry($shippingAddress->countryCode);

        Log::info(json_encode($states));

        if (empty($states)) {
            return $order;
        }

        return $this->setShippingAddressStateCode($order, $states);
    }


    /**
     * setShippingAddressStateCode
     * @param Order $order
     * @param array $states
     * @return Order
     */
    protected function setShippingAddressStateCode(Order $order, array $states): Order
    {
        Log::info(__METHOD__);
        $shippingAddress = $order->shippingAddress;
        $stateName = Str::ascii($shippingAddress->state);

        $state = collect($states)->first(
            function ($state) use ($shippingAddress, $stateName) {

                //match State Name with and without accents
                if ((isset($state['name']) && $state['name'] === ucfirst($shippingAddress->state))
                || (isset($state['name']) && $state['name'] === ucfirst($stateName))
                ) {
                    return $state;
                }

                //check if state code was pass has state name
                if (isset($state['code']) && $state['code'] === strtoupper($shippingAddress->state)) {
                    return $state;
                }
            }
        );

        Log::info('Pre Shipping Address Update');
        Log::info(json_encode($order->shippingAddress));
        Log::info(json_encode($state));

        if ($state === null || !isset($state['code'])) {
            return $order;
        }

        $order->shippingAddress->stateCode = $state['code'];
        Log::info('Post Shipping Address Update');
        Log::info(json_encode($order->shippingAddress));

        return $order;
    }
}
