<?php declare(strict_types=1);

namespace App\Shopify\Http\Controllers\FulfillmentService;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StockItem;

/**
 * Class StockController
 * @package App\Shopify\Http\Controllers
 */
class StockController extends Controller
{
    private $stockItem;
    public function __construct(StockItem $stockItem)
    {
        $this->stockItem = $stockItem;
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(
        Request $request
    ): JsonResponse {
        $params = $request->all();
        $warehouseCode = $request->route('warehouse_code');
        $query = $this->stockItem::query()
            ->selectRaw("qty + allocated_qty as 'qty', sku")
            ->where('source_id', $warehouseCode);
        if (isset($params['sku'])) {
            $sku = [
                        $params['sku'],
                        strlen($params['sku']) == 13
                            ? substr($params['sku'], 1)
                            : str_pad($params['sku'], 13, '0', STR_PAD_LEFT)
                   ];
            $query->whereIn('sku', $sku);
            $result = $query->first();
            return new JsonResponse(
                $result ? [
                    $params['sku'] => $result['qty']
                ] : []
            );
        }
        $plucked = $query->pluck('qty', 'sku');

        //@todo add support of xml format
        return new JsonResponse(
            $this->filterResult($plucked->all()),
            200
        );
    }

    private function filterResult($skus)
    {
        //support 12 or 13 digits mode
        $len = count($skus);
        $count = 0;
        foreach ($skus as $sku => $qty) {
            $sku = (string) $sku;
            $newSku = strlen($sku) == 13
                ? substr($sku, 1)
                : str_pad($sku, 13, '0', STR_PAD_LEFT);
            $skus[$newSku] = $qty;
            $count++;
            if ($count>=$len) {
                break;
            }
        }
        return $skus;
    }
}
