<?php

namespace App\Core\Services\Mutators\TaxId\PassThrough;

use App\Core\Services\Mutators\FormatterInterface;

/**
 * Class PassThroughTaxIdFormatter
 * @package App\Core
 */
class PassThroughTaxIdFormatter implements FormatterInterface
{
    /**
     * Return a Tax ID without mutator
     *
     * @param string $taxId
     * @return string
     */
    public function format(string $taxId): string
    {
        return $taxId;
    }
}
