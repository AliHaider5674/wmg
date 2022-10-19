<?php

namespace App\Core\Services\Mutators\TaxId;

use App\Core\Services\Mutators\TaxId\PassThrough\PassThroughTaxIdFormatter;
use App\Core\Services\Mutators\TaxId\PassThrough\PassThroughTaxIdValidator;
use App\Core\Services\Mutators\ValueMutator;

/**
 * This factory will create a Mutator object with the necessary validator and
 * formatter for a specific countries' tax ID.
 *
 * Class TaxIdMutatorFactory
 * @package App\Core
 */
class TaxIdMutatorFactory
{
    /**
     * Default Validator for countries that are not defined
     */
    private const DEFAULT_VALIDATOR = PassThroughTaxIdValidator::class;

    /**
     * Default Formatter for countries that are not defined
     */
    private const DEFAULT_FORMATTER = PassThroughTaxIdFormatter::class;

    /**
     * A list of validators classes with their respective country as the key
     *
     * @var array
     */
    private $validators;

    /**
     * A list of formatter classes with their respective country as the key
     *
     * @var array
     */
    private $formatters;

    /**
     * ValueFormatterFactory constructor.
     *
     * @param array $formatters
     * @param array $validators
     */
    public function __construct(array $formatters, array $validators)
    {
        $this->formatters = $formatters;
        $this->validators = $validators;
    }

    /**
     * Create a ValueMutator for Brazilian Tax IDs
     *
     * @param string $countryCode
     * @return ValueMutator
     */
    public function create(string $countryCode): ValueMutator
    {
        $countryCode = strtoupper($countryCode);

        return app()->makeWith(ValueMutator::class, [
            'validator' => app(
                $this->getValidatorClassForCountryCode($countryCode)
            ),
            'formatter' => app(
                $this->getformatterClassForCountryCode($countryCode)
            ),
        ]);
    }

    /**
     * @param string $countryCode
     * @return string
     */
    private function getValidatorClassForCountryCode(string $countryCode): string
    {
        return $this->validators[$countryCode]
            ?? self::DEFAULT_VALIDATOR;
    }

    /**
     * @param string $countryCode
     * @return string
     */
    private function getFormatterClassForCountryCode(string $countryCode): string
    {
        return $this->formatters[$countryCode]
            ?? self::DEFAULT_FORMATTER;
    }
}
