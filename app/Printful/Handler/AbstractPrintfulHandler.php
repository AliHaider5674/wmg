<?php declare(strict_types=1);

namespace App\Printful\Handler;

use App\Core\Handlers\AbstractHandler;
use App\Printful\Exceptions\MethodNotImplementedException;

/**
 * Class AbstractPrintfulHandler
 * @package App\Printful\Handler
 */
class AbstractPrintfulHandler extends AbstractHandler
{
    /**
     * @throws MethodNotImplementedException
     */
    public function handle(): void
    {
        if (!method_exists($this, 'handleParameter')) {
            throw new MethodNotImplementedException(
                "The handleParameter method is not implemented in this handler method."
            );
        }

        $this->ioAdapter->start();
        $this->ioAdapter->receive([$this, 'handleParameter']);
        $this->ioAdapter->finish();
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * @param $item
     * @throws MethodNotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function rollbackItem($item, ...$args): void
    {
        throw new MethodNotImplementedException(
            "The rollbackItem method is not implemented in this IO class"
        );
    }
}
