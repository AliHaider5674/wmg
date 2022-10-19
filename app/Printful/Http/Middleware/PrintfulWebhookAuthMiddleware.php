<?php declare(strict_types=1);

namespace App\Printful\Http\Middleware;

use App\Printful\Configurations\PrintfulConfig;
use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class PrintfulWebhookAuthMiddleware
 * @package App\Printful\Http\Middleware
 */
class PrintfulWebhookAuthMiddleware
{
    /**
     * @var PrintfulConfig
     */
    private $printfulConfig;

    /**
     * PrintfulWebhookAuthMiddleware constructor.
     * @param PrintfulConfig $printfulConfig
     */
    public function __construct(PrintfulConfig $printfulConfig)
    {
        $this->printfulConfig = $printfulConfig;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->key !== $this->printfulConfig->getWebhookKey()) {
            throw new UnauthorizedHttpException("Invalid key");
        }

        return $next($request);
    }
}
