<?php

namespace App\IMMuleSoft\Handler\Processor;

use App\Catalog\Models\ProductDimension;
use App\Core\Services\EventService;
use App\IMMuleSoft\Constants\ProductDimensionConstant;
use App\IMMuleSoft\Handler\Processor\Traits\RequestProcessor;
use App\IMMuleSoft\Models\ImMulesoftRequest;
use App\Catalog\Models\Product as ProductModel;
use App\IMMuleSoft\Models\Service\ModelBuilder\ResponseMessageBuilder;

/**
 * Class Processor
 * @package App\IMMuleSoft\Handler\Product
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Product implements ProcessorInterface
{
    use RequestProcessor;

    const ERROR_RATE_PERCENTAGE = 0.30;

    private ResponseMessageBuilder $responseMessageBuilder;
    private EventService $eventService;

    public function __construct(
        ResponseMessageBuilder $responseMessageBuilder,
        EventService $eventService
    ) {
        $this->responseMessageBuilder = $responseMessageBuilder;
        $this->eventService = $eventService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImMulesoftRequest $request)
    {
        $results = $this->getRequestData($request);

        if (!$results['status']) {
            return;
        }

        $requestData = $results['data'];

        $this->updateJobStatus($request, ImMulesoftRequest::STATUS_PROCESSING);

        $totalRequestElements = count($requestData);
        $successElementsProcessed = 0;

        foreach ($requestData as $requestElement) {
            $sku = trim($requestElement->sku);

            $productDimension = ProductDimension::query()
            ->where('product_sku', '=', $sku)
            ->where('type', '=', ProductDimensionConstant::TYPE_WEIGHT)
            ->where('unit', '=', ProductDimensionConstant::UNIT_WEIGHT_GRAM)
            ->get()->first();

            if ($productDimension === null) {
                $product = ProductModel::query()
                    ->where('sku', '=', $sku)
                    ->get()->first();

                if ($product === null) {
                    $product = new ProductModel();
                    $product->fill(
                        [
                            'sku' => $sku,
                            'name' => '<placeholder>'
                        ]
                    )->save();
                }

                $model = new ProductDimension();
                $model->product_sku = $sku;
                $model->type = ProductDimensionConstant::TYPE_WEIGHT;
                $model->unit = ProductDimensionConstant::UNIT_WEIGHT_GRAM;
                $model->value = $requestElement->weight;
                $model->save();
                $successElementsProcessed++;

                continue;
            }

            if (isset($requestElement->weight)) {
                $productDimension->value = $requestElement->weight;
                $productDimension->save();
                $successElementsProcessed++;
            }
        }

        if (($successElementsProcessed / $totalRequestElements) < self::ERROR_RATE_PERCENTAGE) {
            $request->attempts = $request->attempts++;
            $request->status = ImMulesoftRequest::STATUS_ERROR;
            $request->save();
            return;
        }

        $request->status = ImMulesoftRequest::STATUS_COMPLETE;
        $request->save();
    }
}
