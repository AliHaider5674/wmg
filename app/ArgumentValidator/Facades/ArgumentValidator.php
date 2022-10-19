<?php

namespace App\ArgumentValidator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ArgumentValidator
 * @package App\ArgumentValidator
 * @method static assureType($variable, array $types, bool $throwException = true): bool
 */
class ArgumentValidator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'argumentValidator';
    }
}
