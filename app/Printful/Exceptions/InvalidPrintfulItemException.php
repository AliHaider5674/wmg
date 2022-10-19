<?php declare(strict_types=1);

namespace App\Printful\Exceptions;

use Throwable;

/**
 * Class InvalidPrintfulItemException
 * @package App\Printful\Exceptions
 */
class InvalidPrintfulItemException extends PrintfulException
{
    /**
     * @var string
     */
    private $printfulItemClass;

    /**
     * @var array
     */
    private $rawData;

    /**
     * InvalidPrintfulItemException constructor.
     * @param string         $printfulItemClass
     * @param array          $rawData
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $printfulItemClass = "",
        array $rawData = [],
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->printfulItemClass = $printfulItemClass;
        $this->rawData = $rawData;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getPrintfulItemClass(): string
    {
        return $this->printfulItemClass;
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }
}
