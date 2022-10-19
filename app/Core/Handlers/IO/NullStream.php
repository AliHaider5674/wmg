<?php declare(strict_types=1);

namespace App\Core\Handlers\IO;

/**
 * Class NullIO
 *
 * Implementing a null object pattern in order to prevent from switching the
 * position of the arguments in the handler constructor signature while also
 * not resolving things using app()->make(); - which is known as the service
 * locator anti-pattern.
 *
 * @package App\Core\Handler\IO
 */
class NullStream implements IOInterface
{
    /**
     * @param array|null $data
     */
    public function start(array $data = null): void
    {
        // Null
    }

    /**
     * @param array|null $data
     */
    public function finish(array $data = null): void
    {
        // Null
    }

    /**
     * @param $callback
     */
    public function receive($callback): void
    {
        // Null
    }

    /**
     * @param      $data
     * @param null $callback
     */
    public function send($data, $callback = null): void
    {
        // Null
    }

    /**
     * @param mixed ...$args
     */
    public function rollback(...$args): void
    {
        // Null
    }
}
