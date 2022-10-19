<?php declare(strict_types=1);

namespace App\Printful\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Printful\Repositories\PrintfulEventRepository;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class PrintfulWebhookController
 * @package App\Printful\Http\Controllers
 */
class PrintfulWebhookController extends Controller
{
    /**
     * @var PrintfulEventRepository
     */
    private $repository;

    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * PrintfulWebhookController constructor.
     * @param PrintfulEventRepository $repository
     * @param ExceptionHandler        $exceptionHandler
     */
    public function __construct(
        PrintfulEventRepository $repository,
        ExceptionHandler $exceptionHandler
    ) {
        $this->repository = $repository;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(
        Request $request
    ): JsonResponse {
        try {
            $this->repository->createEvent($request->toArray());
        } catch (Exception $exception) {
            $this->exceptionHandler->report($exception);

            return new JsonResponse([
                'success' => 'false',
            ], 500);
        }

        return new JsonResponse([
            'success' => 'true',
        ], 201);
    }
}
