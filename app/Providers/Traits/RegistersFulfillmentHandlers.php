<?php declare(strict_types=1);

namespace App\Providers\Traits;

use App\Core\Handlers\FulfillmentHandlerContainer;
use InvalidArgumentException;

/**
 * Class RegistersFulfillmentHandlers
 * @package App\Providers\Traits
 */
trait RegistersFulfillmentHandlers
{
    /**
     * @var FulfillmentHandlerContainer
     */
    private $fulfillmentHandlerContainer;

    /**
     * @param string $type
     * @param        $className
     */
    protected function registerFulfillmentHandler(string $type, $className): void
    {
        $this->getFulfillmentHandlerContainer()->registerHandler($type, $className);
    }

    /**
     * @return FulfillmentHandlerContainer
     */
    protected function getFulfillmentHandlerContainer(): FulfillmentHandlerContainer
    {
        if (!isset($this->app)) {
            throw new InvalidArgumentException("This trait should only be used in a service provider.");
        }

        return $this->fulfillmentHandlerContainer
            ?? $this->fulfillmentHandlerContainer = $this->app->make(FulfillmentHandlerContainer::class);
    }
}
