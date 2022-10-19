<?php declare(strict_types=1);

namespace App\Enums;

use App\Core\Enums\BaseEnum;
use App\Filters\FilterInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Class FilterEnum
 * @package App\Enums
 */
abstract class FilterEnum extends BaseEnum implements FilterInterface
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var string
     */
    protected $modifier = '=';

    /**
     * Return a closure that modifies the query object
     *
     * @return Closure
     */
    public function filter(): Closure
    {
        if (!isset($this->column)) {
            throw new InvalidArgumentException("You must define a column property.");
        }

        if (!isset($this->column)) {
            throw new InvalidArgumentException("The modifier property must not be null.");
        }

        return Closure::fromCallable(function (Builder $query) {
            $query->where($this->column, $this->modifier, $this->value);
        });
    }
}
