<?php declare(strict_types=1);

namespace App\Printful\Converter\Local\OrderAddress;

use App\Core\Models\RawData\OrderAddress;
use App\Printful\Converter\AbstractRawDataConverter;
use App\Printful\Structures\RecipientCreationParameters;

/**
 * Class ToRecipientCreationParameters
 * @package App\Printful\Converter\Local\OrderAddress
 */
class ToRecipientCreationParameters extends AbstractRawDataConverter
{
    /**
     * Mapping between OrderAddress attributes and OrderCreationParameters
     * attributes
     */
    private const ADDRESS_ATTRIBUTE_MAP = [
        'address1' => 'address1',
        'address2' => 'address2',
        'city' => 'city',
        'state' => 'stateName',
        'zip' => 'zip',
        'countryCode' => 'countryCode',
        'stateCode' => 'stateCode',
        'phone' => 'phone',
        'email' => 'email',
    ];

    /**
     * @param OrderAddress $address
     * @return RecipientCreationParameters
     */
    public function convert(
        OrderAddress $address
    ): RecipientCreationParameters {
        $recipient = new RecipientCreationParameters();

        $recipient->name = $address->customerName;
        $taxId = $address->customAttributes['tax_id'] ?? null;

        if ($taxId) {
            $recipient->company = $taxId;
        }

        $recipient = $this->mapParameters(
            $address,
            $recipient,
            self::ADDRESS_ATTRIBUTE_MAP
        );

        return $recipient;
    }
}
