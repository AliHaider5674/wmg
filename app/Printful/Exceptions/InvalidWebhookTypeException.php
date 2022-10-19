<?php declare(strict_types=1);

namespace App\Printful\Exceptions;

use Throwable;

/**
 * Class InvalidWebhookTypeException
 * @package App\Printful\Exceptions
 */
class InvalidWebhookTypeException extends PrintfulException
{
    /**
     * @var string
     */
    private $webhookType;

    /**
     * InvalidWebhookTypeException constructor.
     * @param string         $webhookType
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $webhookType = "",
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->webhookType = $webhookType;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getWebhookType(): string
    {
        return $this->webhookType;
    }
}
