<?php

namespace App\Core\Services\Mutators\Example;

use App\Core\Services\Mutators\FormatterInterface;

/**
 * All Formatter classes must implement the FormatterInterface
 *
 * Class ExampleFormatter
 * @package App\Core
 */
class ExampleFormatter implements FormatterInterface
{
    /**
     * Format a value
     *
     * The only method you need for this class is the public `formatValue()`
     * method. Here you will format the string to the desired format. Note that
     * the string will already have been validated by this point, so you can
     * safely assume that it matches the necessary format.
     *
     * You should not put any logic inside method. All validation logic must go
     * inside the validator class.
     *
     * @param string $value
     * @return string
     */
    public function format(string $value): string
    {
        return strtoupper($value);
    }
}
