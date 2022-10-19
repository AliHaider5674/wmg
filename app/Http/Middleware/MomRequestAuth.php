<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use MomApi\Middleware\Authentication;

/**
 * Mom auth
 *
 * Class MomRequestAuth
 * @category WMG
 * @package  App\Http\Middleware
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class MomRequestAuth
{
    private $auth;
    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle incoming requests
     * @param $request
     * @param \Closure $next
     * @param array    ...$guards
     *
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (!$this->auth->isAuthenticated()) {
            throw new AuthenticationException('Unauthorized', $guards, null);
        }
        return $next($request);
    }
}
