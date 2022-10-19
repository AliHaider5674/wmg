<?php

namespace App\ArgumentValidator;

use InvalidArgumentException;

/**
 * Class ArgumentValidator
 * @package App\ArgumentValidator
 */
class ArgumentValidator
{
    /**
     * Format for the exception message that is used when argument is not of one
     * of the expected types
     */
    private const EXCEPTION_MESSAGE_FORMAT = 'Argument for %s must be one of the following types: %s. %s provided.';

    /**
     * @param       $variable
     * @param array $types
     * @param bool  $throwException
     * @return bool
     */
    public function assureType(
        $variable,
        array $types
    ): bool {
        $type = gettype($variable);
        if (in_array($type, $types, true)) {
            return true;
        }
        return false;
    }

    /**
     * @param       $variable
     * @param array $types
     * @return bool
     */
    public function assureTypeWithException(
        $variable,
        array $types
    ): bool {
        $type = gettype($variable);
        if (in_array($type, $types, true)) {
            return true;
        }
        throw new InvalidArgumentException(sprintf(
            self::EXCEPTION_MESSAGE_FORMAT,
            $this->getCallingMethodName(),
            implode(",  ", $types),
            $type
        ));
    }

    /**
     * @return string|null
     */
    private function getCallingMethodName(): ?string
    {
        $backtrace = debug_backtrace()[2] ?? [];

        return !empty($backtrace['class'])
            ? $backtrace['class'] . '::' . $backtrace['function']
            : $backtrace['function'] ?? null;
    }
}
