<?php

namespace App\Core\Services\Mutators\TaxId\PassThrough;

use App\Core\Services\Mutators\AbstractValidator;

/**
 * class PassThroughTaxIdValidator
 * @package App\Core
 */
class PassThroughTaxIdValidator extends AbstractValidator
{
    /**
     * Return true
     *
     * @param string $taxId
     * @return bool
     */
    protected function isValid(string $taxId): bool
    {
        return true;
    }
}
