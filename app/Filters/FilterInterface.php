<?php declare(strict_types=1);

namespace App\Filters;

use Closure;

/**
 * Interface FilterInterface
 * @package App\Filter
 */
interface FilterInterface
{
    /**
     * Return a closure that accepts a Builder instance
     *
     * @return Closure
     */
    public function filter(): Closure;
}
