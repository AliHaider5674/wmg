<?php declare(strict_types=1);

namespace App\Shopify\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class FulfillmentServiceController
 * @package App\Shopify\Http\Controllers
 */
class FulfillmentServiceController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'success' => 'true',
        ], 201);
    }
}
