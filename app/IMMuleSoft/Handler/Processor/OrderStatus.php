<?php

namespace App\IMMuleSoft\Handler\Processor;

use App\Core\Services\EventService;
use App\Exceptions\RecordExistException;
use App\IMMuleSoft\Handler\Processor\Traits\RequestProcessor;
use App\IMMuleSoft\Models\ImMulesoftRequest;
use App\IMMuleSoft\Handler\Processor\OrderStatus\Shipment;
use App\IMMuleSoft\Models\Service\ModelBuilder\ResponseMessageBuilder;

/**
 * Class OrderStatus
 * @package App\IMMuleSoft\Handler\Processor
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderStatus implements ProcessorInterface
{
    use RequestProcessor;

    const STATUS_COMPLETE = 'Complete';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_CANCELLED = 'Cancelled';

    const ERROR_RATE_PERCENTAGE = 0.30;

    private Shipment $shipment;
    private ResponseMessageBuilder $responseMessageBuilder;
    private EventService $eventService;

    /**
     * @param Shipment $shipment
     * @param ResponseMessageBuilder $responseMessageBuilder
     * @param EventService $eventService
     */
    public function __construct(
        Shipment $shipment,
        ResponseMessageBuilder $responseMessageBuilder,
        EventService $eventService
    ) {
        $this->shipment = $shipment;
        $this->responseMessageBuilder = $responseMessageBuilder;
        $this->eventService = $eventService;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws RecordExistException
     */
    public function handle(ImMulesoftRequest $request)
    {
        $results = $this->getRequestData($request);

        if (!$results['status']) {
            return;
        }

        $requestData = $results['data'];

        $this->updateJobStatus($request, ImMulesoftRequest::STATUS_PROCESSING);

        $totalNumberOfOrderStatues = count($requestData);
        $successOrderStatusProcessed = 0;

        foreach ($requestData as $orderStatus) {
            switch ($orderStatus->status) {
                case self::STATUS_COMPLETE:
                case self::STATUS_PROCESSING:
                    //build shipment/ack model
                    $parameters = $this->shipment->getParameters($orderStatus);
                    $isSuccessful = $this->shipment->processParameters($parameters);
                    if (!$isSuccessful) {
                        //Todo log and report to Ingram
                        break;
                    }
                    $successOrderStatusProcessed++;
                    break;
                case self::STATUS_CANCELLED:
                    break;
            }
        }

        if (($successOrderStatusProcessed / $totalNumberOfOrderStatues) < self::ERROR_RATE_PERCENTAGE) {
            $request->attempts = $request->attempts++;
            $request->status = ImMulesoftRequest::STATUS_ERROR;
            $request->save();
            return;
        }

        $request->status = ImMulesoftRequest::STATUS_COMPLETE;
        $request->save();
    }
}
