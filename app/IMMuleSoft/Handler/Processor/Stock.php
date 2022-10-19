<?php

namespace App\IMMuleSoft\Handler\Processor;

use App\Core\Services\EventService;
use App\IMMuleSoft\Constants\ConfigConstant;
use App\IMMuleSoft\Models\ImMulesoftRequest;
use App\IMMuleSoft\Models\Service\ModelBuilder\ResponseMessageBuilder;
use App\IMMuleSoft\Handler\Processor\Traits\RequestProcessor;

/**
 * Class Stock
 * @package App\IMMuleSoft\Handler\Processor
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Stock implements ProcessorInterface
{
    use RequestProcessor;

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
     * handle
     * @param ImMulesoftRequest $request
     */
    public function handle(ImMulesoftRequest $request)
    {
        $results = $this->getRequestData($request);

        if (!$results['status']) {
            return;
        }

        $requestData = $results['data'];

        $this->updateJobStatus($request, ImMulesoftRequest::STATUS_PROCESSING);

        //iterate through stock levels
        //save to stock item table with sourceId

        $data = [];
        foreach ($requestData as $stockLevel) {
            $data[] = [
                'sku' => $stockLevel->sku,
                'qty' => $stockLevel->quantityGood,
                'source_id' => ConfigConstant::IMMULESOFT_SOURCE_ID,
            ];
        }

        if (!empty($data)) {
            $isUpdateSuccess = $this->updateDatabase($data);

            $request->status =
                ($isUpdateSuccess) ? ImMulesoftRequest::STATUS_COMPLETE : ImMulesoftRequest::STATUS_ERROR;
            $request->save();
        }
    }

    /**
     * updateData
     * @param $data
     * @return bool
     */
    protected function updateDatabase($data): bool
    {
         return insertOrUpdateSql($data, 'stock_items');
    }
}
