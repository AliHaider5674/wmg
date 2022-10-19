<?php declare(strict_types=1);

namespace App\Core\Enums;

use BenSampo\Enum\Enum;
use Illuminate\Support\Str;

/**
 * Class BaseEnum
 * @package App\Core\Enums
 */
class BaseEnum extends Enum
{
    /**
     * Check that the enum contains a specific key.
     *
     * Allow you to define enum constants as SCREAMING_SNAKE_CASE and refer to
     * them using camelCase methods
     *
     * @param  string  $key
     * @return bool
     */
    public static function hasKey(string $key): bool
    {
        $snakeKey = Str::snake($key);

        return count(
            array_intersect(
                [$key, $snakeKey, strtoupper($snakeKey)],
                static::getKeys()
            )
        ) > 0;
    }

    /**
     * Get the value for a single enum key
     *
     * Allow you to define enum constants as SCREAMING_SNAKE_CASE and refer to
     * them using camelCase methods
     *
     * @param  string  $key
     * @return mixed
     */
    public static function getValue(string $key)
    {
        $snakeKey = Str::snake($key);
        $constants = static::getConstants();

        return $constants[$key]
            ?? $constants[$snakeKey]
            ?? $constants[strtoupper($snakeKey)];
    }
}
