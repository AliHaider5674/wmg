<?php

namespace App\Core\Services\Mutators;

use App\Core\Exceptions\Mutators\MutatorException;

/**
 * Interface FormatterInterface
 * @package App\Core
 */
interface FormatterInterface
{
    /**
     * Format a string
     *
     * @param string $value
     * @return string
     * @throws MutatorException
     */
    public function format(string $value): string;
}
