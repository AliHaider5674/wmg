<?php

namespace App\Core\Services\Mutators\TaxId\Brazil;

use App\Core\Services\Mutators\AbstractValidator;

/**
 * class BrazilTaxIdValidator
 * @package App\Core
 */
class BrazilTaxIdValidator extends AbstractValidator
{
    /**
     * Regex for valid characters in ta Brazil Tax ID
     */
    private const VALID_CHARACTERS_REGEX = '/^[0-9.\/-]+$/';

    /**
     * List of valid number of digits that a Brazil tax ID should have.
     */
    private const VALID_DIGIT_COUNTS = [11, 14];

    /**
     * Error message for a tax ID that contains invalid characters
     */
    private const INVALID_CHARACTERS_MESSAGE = 'The tax ID must only contain the following characters: 0-9 . / -';

    /**
     * Error message when the tax ID length in digits is not valid
     */
    private const INVALID_LENGTH_MESSAGE = 'The tax ID must contain either 11 or 14 digits.';

    /**
     * @param string $taxId
     * @return bool
     */
    protected function isValid(string $taxId): bool
    {
        if ($this->containsInvalidCharacters($taxId)) {
            $this->addError(self::INVALID_CHARACTERS_MESSAGE);
        }

        if ($this->containsInvalidNumberOfDigits($taxId)) {
            $this->addError(self::INVALID_LENGTH_MESSAGE);
        }

        return $this->hasErrors() === false;
    }

    /**
     * Check if a tax ID contains invalid characters
     *
     * @param string $taxId
     * @return bool
     */
    private function containsInvalidCharacters(string $taxId): bool
    {
        return !preg_match(self::VALID_CHARACTERS_REGEX, $taxId);
    }

    /**
     * Check if the string contains an invalid number of digits
     *
     * @param string $taxId
     * @return bool
     */
    private function containsInvalidNumberOfDigits(string $taxId): bool
    {
        $digitCount = $this->countDigitsInString($taxId);

        return in_array($digitCount, self::VALID_DIGIT_COUNTS, true) === false;
    }

    /**
     * Count total number of digits in string
     *
     * @param $string
     * @return int
     */
    private function countDigitsInString($string): int
    {
        return preg_match_all('/[0-9]/', $string) ?: 0;
    }
}
