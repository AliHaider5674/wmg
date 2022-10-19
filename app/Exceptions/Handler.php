<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

/**
 * Error handler
 *
 * Class Handler
 * @category WMG
 * @package  App\Exceptions
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param string    $message
     * @param Throwable $exception
     */
    public function logException(string $message, Throwable $exception): void
    {
        Log::error($message, array_merge(
            $this->context(),
            ['exception' => $exception]
        ));
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Throwable $exception
     * @return Response|mixed
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception)) {
            $statusCode = $exception->getStatusCode();
            switch ($statusCode) {
                case '404':
                    return response('Api not found.', 404);
            }
        }
        return parent::render($request, $exception);
    }
}
