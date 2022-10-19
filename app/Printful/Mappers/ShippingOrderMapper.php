<?php declare(strict_types=1);

namespace App\Printful\Mappers;

use App\Core\Mappers\ShippingOrderMapper as BaseShippingOrderMapper;

/**
 * Class ShippingOrderMapper
 * @package App\Printful\Mappers
 */
class ShippingOrderMapper extends BaseShippingOrderMapper
{
    /**
     * @return array
     */
    protected function getShippingMethodMap(): array
    {
        return [
            "17" => "STANDARD",
            "*" => "STANDARD"
        ];
    }
}
