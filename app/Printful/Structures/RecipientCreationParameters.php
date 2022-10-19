<?php declare(strict_types=1);

namespace App\Printful\Structures;

use Printful\Structures\Order\RecipientCreationParameters as BaseRecipientCreationParameters;

/**
 * Class RecipientCreationParameters
 * @package App\Printful\Structures
 */
class RecipientCreationParameters extends BaseRecipientCreationParameters
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        $return = parent::toArray();

        foreach ($return as $key => $value) {
            if ($value === null) {
                unset($return[$key]);
            }
        }

        return $return;
    }
}
