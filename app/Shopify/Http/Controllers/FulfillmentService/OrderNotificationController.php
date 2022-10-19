<?php declare(strict_types=1);

namespace App\Shopify\Http\Controllers\FulfillmentService;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class FulfillmentServiceController
 * @package App\Shopify\Http\Controllers
 */
class OrderNotificationController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'success' => 'true',
        ], 201);
    }
}
