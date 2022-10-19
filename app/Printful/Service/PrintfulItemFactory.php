<?php declare(strict_types=1);

namespace App\Printful\Service;

use App\Printful\Exceptions\InvalidPrintfulItemException;
use Printful\Structures\BaseItem;
use Printful\Exceptions\PrintfulException;

/**
 * Class PrintfulItemFactory
 * @package App\Printful\Service
 */
class PrintfulItemFactory
{
    /**
     * @param string $itemClass
     * @param array  $rawData
     * @return BaseItem
     * @throws InvalidPrintfulItemException
     * @throws PrintfulException
     */
    public function makeItem(string $itemClass, array $rawData): BaseItem
    {
        if (!is_subclass_of($itemClass, BaseItem::class)) {
            throw new InvalidPrintfulItemException(
                $itemClass,
                $rawData,
                "Item class is not a subclass of BaseItem."
            );
        }

        return $itemClass::fromArray($rawData);
    }
}
