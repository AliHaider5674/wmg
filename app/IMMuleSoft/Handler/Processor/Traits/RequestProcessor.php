<?php

namespace App\IMMuleSoft\Handler\Processor\Traits;

use App\IMMuleSoft\Constants\EventConstant;
use App\IMMuleSoft\Models\ImMulesoftRequest;

trait RequestProcessor
{
    /**
     * updateJobStatus
     * @param ImMulesoftRequest $request
     * @param string $status
     */
    public function updateJobStatus(ImMulesoftRequest $request, string $status)
    {
        $request->status = $status;
        $request->save();
    }

    /**
     * getRequestData
     * @param ImMulesoftRequest $request
     * @return array
     */
    public function getRequestData(ImMulesoftRequest $request): array
    {
        $result = array();
        $result['status'] = false;
        $result['data'] = array();

        //decode json data
        if (!empty($request->data)) {
            $result['data'] = json_decode($request->data);

            //handle decoding errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                // JSON is inValid
                $this->updateJobStatus($request, ImMulesoftRequest::STATUS_ERROR);
                return $result;
            }
        }

        $result['status'] = true;
        return $result;
    }

    /**
     * response
     * @param array $responseItems
     * @param $statusCode
     * @param string $message
     * @param string $resourceType
     */
    protected function response(
        array $responseItems,
        $statusCode,
        string $message,
        string $resourceType
    ) {
        $this->eventService->dispatchEvent(
            EventConstant::EVENT_IMMULESOFT_RESPONSE_MESSAGE,
            $this->responseMessageBuilder->build(
                $statusCode,
                $message,
                $resourceType,
                $responseItems
            )
        );
    }
}
