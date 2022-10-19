<?php

namespace App\Core\Exceptions\Mutators;

use Throwable;

/**
 * Class ValidationException
 * @package App\Core
 */
class ValidationException extends MutatorException
{
    /**
     * @var array
     */
    private $errors;

    /**
     * ValidationException constructor.
     *
     * @param array          $errors
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(array $errors = [], $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        $message = reset($errors) ?: "";

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get an array of errors with validating a string
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
