<?php declare(strict_types=1);

namespace App\Core\Mappers;

use App\Core\Models\RawData\Order;
use Sokil\IsoCodes\IsoCodesFactory;
use Illuminate\Support\Str;

/**
 * Class StateCodeMapper
 * @package App\Core\Mappers
 */
class StateCodeMapper implements OrderProcessorInterface, ConditionallyRuns
{
    /**
     * Country codes that this should run for
     */
    private const APPLICABLE_COUNTRY_CODES = ['JP', 'US', 'CA', 'AU'];

    /**
     * @var IsoCodesFactory
     */
    private $isoCodesFactory;

    /**
     * StateCodeMapper constructor.
     * @param IsoCodesFactory $isoCodesFactory
     */
    public function __construct(IsoCodesFactory $isoCodesFactory)
    {
        $this->isoCodesFactory = $isoCodesFactory;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function shouldRun(Order $order): bool
    {
        return in_array(
            $order->shippingAddress->countryCode,
            self::APPLICABLE_COUNTRY_CODES,
            true
        );
    }

    /**
     * @param Order $order
     * @return Order
     */
    public function processOrder(Order $order): Order
    {
        $shippingAddress = $order->shippingAddress;

        if (empty($shippingAddress->state)) {
            return $order;
        }

        $subdivisions = $this->isoCodesFactory->getSubdivisions(
            IsoCodesFactory::OPTIMISATION_MEMORY
        )->getAllByCountryCode(
            $shippingAddress->countryCode
        );

        $subdivision = collect($subdivisions)->first(
            function ($subdivision) use ($shippingAddress) {

                if ($subdivision->getName() === $shippingAddress->state) {
                    return $subdivision;
                }

                //check if state code was pass has state
                $code = sprintf(
                    '%s-%s',
                    strtoupper($shippingAddress->countryCode),
                    strtoupper($shippingAddress->state)
                );

                if ($subdivision->getCode() === $code) {
                    return $subdivision;
                }

                //test state name without character accents
                $code = Str::slug($shippingAddress->state);

                if ($subdivision->getName() === ucfirst($code)) {
                    return $subdivision;
                }
            }
        );

        if ($subdivision === null) {
            return $order;
        }

        $codeParts = explode('-', $subdivision->getCode());
        $stateCode = array_pop($codeParts);
        $order->shippingAddress->stateCode = $stateCode;

        return $order;
    }
}
