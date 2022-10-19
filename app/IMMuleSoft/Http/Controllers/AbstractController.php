<?php

namespace App\IMMuleSoft\Http\Controllers;

use App\Http\Controllers\Controller;
use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Models\ImMulesoftRequest;
use App\IMMuleSoft\Repositories\ImMulesoftRequestRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class AbstractController
 * @package App\IMMuleSoft\Http\Controllers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
abstract class AbstractController extends Controller
{
    protected string $controllerType = '';
    protected string $resourceType = '';
    private ImMulesoftRequestRepository $requestRepository;

    /**
     * @param ImMulesoftRequestRepository $requestRepository
     */
    public function __construct(
        ImMulesoftRequestRepository $requestRepository
    ) {
        $this->requestRepository = $requestRepository;
    }

    /**
     * buildResponse
     * @param $data
     * @param $statusCode
     * @return JsonResponse
     */
    protected function buildResponse($data, $statusCode): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    /**
     * __invoke
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        //check post data - ensure its stock level data
        $requestData = $request->getContent();

        if (empty($requestData)) {
            return $this->buildResponse(
                [
                'statusCode' => ResourceConstant::STATUS_CODE_NO_DATA,
                'message' => "No data given",
                'resourceType' => $this->resourceType,
                'responses' => array()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $messageId = hash('sha1', $requestData);

        if ($this->isRequestUnique($messageId)) {
            $data = $this->processRequest($request);
            $model = $this->saveRequest($messageId, $data);
            $this->postSave($model);
        }

        $requestItems = $this->decodeRequest($requestData);

        if (empty($requestItems)) {
            return $this->buildResponse(
                [
                    'statusCode' => ResourceConstant::STATUS_CODE_NO_DATA,
                    'message' => "Invalid data",
                    'resourceType' => $this->resourceType,
                    'responses' => array()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        //iterate through request items, to build item level responses
        $responseItems = $this->buildItemLevelResponse($requestItems);

        return $this->buildResponse(
            [
                'statusCode' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
                'message' => ResourceConstant::RESPONSE_MESSAGE_SUCCESS,
                'messageId' => $messageId,
                'resourceType' => $this->resourceType,
                'responses' => $responseItems
            ],
            Response::HTTP_OK
        );
    }

    /**
     * saveRequest
     * @param string $messageId
     * @param array $data
     * @return Model
     */
    protected function saveRequest(string $messageId, array $data): Model
    {
        return $this->requestRepository->create(
            [
                'status' => ImMulesoftRequest::STATUS_RECEIVED,
                'data' => (isset($data['requestData'])) ? $data['requestData'] : '',
                'additional' => (isset($data['additional'])) ? $data['additional'] : '',
                'message_id' => $messageId,
                'resource_type' => $this->resourceType
            ]
        );
    }

    /**
     * isRequestUnique
     * @param string $messageId
     * @return bool
     */
    protected function isRequestUnique(string $messageId): bool
    {
        return $this->requestRepository
            ->isUnique(
                $messageId,
                $this->resourceType
            );
    }

    /**
     * processRequest
     * @param Request $request
     * @return array
     */
    protected function processRequest(Request $request) : array
    {
        $data = array();

        $data['requestData'] = $request->getContent();

        return $data;
    }

    /**
     * postSave
     * @param $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function postSave($model)
    {
    }

    protected function decodeRequest($requestData): array
    {
        $result = array();
        //decode json data
        if (!empty($requestData)) {
            $result = json_decode($requestData);

            //handle decoding errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                // JSON is inValid
                return array();
            }
        }

        return $result;
    }

    protected function buildItemLevelResponse(array $requestItems): array
    {
        $responseItems = array();

        foreach ($requestItems as $item) {
            $responseItems[] = [
                'resourceCode' => $item->code,
                'statusCode' => \Symfony\Component\HttpFoundation\Response::HTTP_OK,
                'message' =>  ResourceConstant::RESPONSE_MESSAGE_SUCCESS
            ];
        }

        return $responseItems;
    }
}
