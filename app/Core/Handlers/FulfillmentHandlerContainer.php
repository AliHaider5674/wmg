<?php declare(strict_types=1);

namespace App\Core\Handlers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Class FulfillmentHandlerContainer
 *
 * This is a wrapper around the service container and therefore should not be
 * used other than in service providers and entry points of your application
 *
 * @package App\Core\Handler
 */
class FulfillmentHandlerContainer
{
    /**
     * @var array
     */
    private $handlerTypes = [];

    /**
     * @var Application
     */
    private $app;

    /**
     * HandlerContainer constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $type
     * @param        $className
     */
    public function registerHandler(string $type, $className): void
    {
        $this->validateClassName($className);

        if (!in_array($type, $this->handlerTypes, true)) {
            $this->handlerTypes[] = $type;
        }

        $this->app->tag($className, $this->getHandlerTypeKey($type));
    }

    /**
     * @param string $type
     * @return iterable
     */
    public function getHandlers(string $type): iterable
    {
        return $this->app->tagged($this->getHandlerTypeKey($type));
    }

    /**
     * @return array
     */
    public function getHandlerTypes(): array
    {
        return $this->handlerTypes;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getHandlerTypeKey(string $type): string
    {
        return sprintf('handler.%s', $type);
    }

    /**
     * @param $className
     * @note Some sort of a polyfill for PHP 8 union types
     */
    private function validateClassName($className): void
    {
        if (!is_string($className)
            && (!is_array($className)
                || !is_string(current($className)))
        ) {
            throw new InvalidArgumentException("Parameter \$className must be either a string or an array of strings.");
        }

        foreach (Arr::wrap($className) as $class) {
            if (!is_subclass_of($class, HandlerInterface::class)) {
                throw new InvalidArgumentException(
                    "Parameter \$className must be a subclass of "
                    . AbstractHandler::class
                );
            }
        }
    }
}
