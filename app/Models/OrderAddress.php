<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Order address model that reference to
 * order_addresses table
 *
 * Class OrderAddress
 * @property mixed $country_code
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderAddress extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $casts = [
        'custom_attributes' => 'array',
    ];

    /**
     * Custom attributes to be included in the model's array or JSON
     * representation
     *
     * @var string[]
     */
    protected $appends = ['tax_id'];

    /**
     * address type
     */
    const CUSTOMER_ADDRESS_TYPE_SHIPPING = 'shipping';
    const CUSTOMER_ADDRESS_TYPE_BILLING = 'billing';
    const CUSTOMER_ADDRESS_TYPE_FIELD = 'customer_address_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'address1', 'address2',
        'city', 'state', 'zip','country_code', 'phone',
        'email','customer_address_type', 'custom_attributes'
    ];

    /**
     * Get customer full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getAttribute('first_name') . ' ' .
            $this->getAttribute('last_name');
    }

    /**
     * Get tax id attribute from custom attributes
     *
     * @return string|null
     */
    public function getTaxIdAttribute(): ?string
    {
        return collect($this->custom_attributes)
            ->firstWhere("name", "tax_id")['value'] ?? null;
    }

    /**
     * Set tax id attribute on custom attributes
     *
     * @param string|null $taxId
     */
    public function setTaxIdAttribute(?string $taxId): void
    {
        $this->custom_attributes = $this->setTaxIdOnCustomAttributesArray(
            $this->custom_attributes ?? [],
            $taxId
        );
    }

    /**
     * Set the tax_id property on a custom attributes array
     *
     * @param array       $customAttributes
     * @param string|null $taxId
     * @return array
     */
    private function setTaxIdOnCustomAttributesArray(
        array $customAttributes,
        ?string $taxId
    ): array {
        $taxIdIndex = collect($customAttributes)
            ->search(static function ($property) {
                return 'tax_id' === ($property['name'] ?? null);
            });

        if ($taxIdIndex !== false) {
            $customAttributes[$taxIdIndex]['value'] = $taxId;

            return $customAttributes;
        }

        $customAttributes[] = [
            'name' => 'tax_id',
            'value' => $taxId,
        ];

        return $customAttributes;
    }
}
