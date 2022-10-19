<?php declare(strict_types=1);

namespace App\Core\Constants;

/**
 * Class BackorderStatusReasonCodes
 * @package App\Core\Constants
 */
class BackorderStatusReasonCodes
{
    /**
     * On hold reason code
     */
    public const ON_HOLD = 'H';

    /**
     * Returned reason code
     */
    public const RETURNED = 'R';

    /**
     * Error reason code
     */
    public const ERROR = 'X';

    /**
     * Not in stock
     */
    const NOT_IN_STOCK = 'NOT_IN_STOCK';
}
