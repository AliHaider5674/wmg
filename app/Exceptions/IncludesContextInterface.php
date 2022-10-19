<?php declare(strict_types=1);

namespace App\Exceptions;

/**
 * Interface IncludesContextInterface
 * @package App\Exceptions
 */
interface IncludesContextInterface
{
    /**
     * Exception context
     *
     * @return array
     */
    public function context(): array;
}
