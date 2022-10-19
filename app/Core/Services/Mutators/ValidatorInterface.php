<?php

namespace App\Core\Services\Mutators;

/**
 * Validate a nonformatted value
 *
 * Interface ValidatorInterface
 * @package App\Core
 */
interface ValidatorInterface
{
    /**
     * Validate that a nonformatted value matches what we're expecting
     *
     * @param string $value
     * @return bool
     */
    public function validate(string $value): bool;

    /**
     * Return errors from the last validation, or an empty array if there were
     * no errors.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Check whether there were any errors from the last validation attempt
     *
     * @return bool
     */
    public function hasErrors(): bool;
}
