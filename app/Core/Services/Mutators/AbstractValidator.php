<?php

namespace App\Core\Services\Mutators;

/**
 * Validate a value before formatting
 *
 * Class AbstractValidator
 * @package App\Core
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * Return any errors with the last validation, or an empty array if none exist
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there were any errors with the last validation
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    /**
     * Validate a string
     *
     * @param string $value
     * @return bool
     */
    public function validate(string $value): bool
    {
        $this->clearErrors();

        return $this->isValid($value);
    }

    /**
     * Method containing actual validation logic, to be overridden by child class
     *
     * @param string $value
     * @return bool
     */
    abstract protected function isValid(string $value): bool;

    /**
     * Add an error to the list of errors
     *
     * @param string $message
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Clear list of errors
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }
}
