<?php

namespace App\Core\Services\Mutators\Example;

use App\Core\Services\Mutators\AbstractValidator;

/**
 * Example Validator
 *
 * All validator classes must extend the AbstractValidator class. You do not
 * need to implement the ValidatorInterface because the AbstractValidator
 * already implements it.
 *
 * Class ExampleValidator
 * @package App\Core
 */
class ExampleValidator extends AbstractValidator
{
    /**
     * Validate a value
     *
     * The method you need in this class is the protected `isValid()` method.
     * The parent AbstractValidator's public method `validate()` will be called,
     * and that method will call this protected `isValid()` method.
     *
     * @param string $value
     * @return bool
     */
    protected function isValid(string $value): bool
    {
        if (!empty($value)) {
            $this->addError("Value is empty");
        }

        if (strlen($value) > 100) {
            $this->addError("Value length over 100 characters");
        }

        return !$this->hasErrors();
    }
}
