<?php

namespace App\Core\Services\Mutators\TaxId\Brazil;

use App\Core\Services\Mutators\FormatterInterface;

/**
 * Class BrazilTaxIdFormatter
 * @package App\Core
 */
class BrazilTaxIdFormatter implements FormatterInterface
{
    /**
     * Prefix for Brazilian tax IDs
     */
    private const BRAZIL_TAX_ID_PREFIX = 'CPF/CNPJ:';

    /**
     * Format a Brazilian Tax ID
     *
     * @param string $taxId
     * @return string
     */
    public function format(string $taxId): string
    {
        $taxIdDigits = preg_replace('/[^0-9]/', '', $taxId);
        $normalizedTaxId = substr_replace($taxIdDigits, '-', -2, 0);

        return self::BRAZIL_TAX_ID_PREFIX . $normalizedTaxId;
    }
}
