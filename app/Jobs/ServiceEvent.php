<?php

namespace App\Jobs;

use App\Events\ServiceFailed;
use App\Exceptions\ServiceException;
use App\Mdc\Service\SoapFaultErrorParser;
use App\Models\ServiceEventCall;
use App\Core\Services\ClientService;
use App\Models\ServiceEventCallResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

/**
 * Queue worker to send out events
 *
 * Class ServiceEvent
 * @category WMG
 * @package  App\Jobs
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ServiceEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ServiceEventCall
     */
    protected $serviceEventCall;

    /**
     * @var string
     */
    private $lastResponse;

    /**
     * @var SoapFaultErrorParser
     */
    protected $errorParser;

    /**
     * ServiceEvent constructor.
     *
     * @param ServiceEventCall     $serviceEventCall
     */
    public function __construct(
        ServiceEventCall $serviceEventCall
    ) {
        $this->serviceEventCall = $serviceEventCall;
    }

    /**
     * Send events
     *
     * @param ClientService        $clientManager
     * @param SoapFaultErrorParser $errorParser
     * @return void
     */
    public function handle(
        ClientService $clientManager,
        SoapFaultErrorParser $errorParser
    ): void {

        $this->errorParser = $errorParser;


        try {
            /** @var \App\Models\ServiceEvent $event */
            unset($this->lastResponse);
            $event = $this->serviceEventCall->serviceEvent;
            $clientName = $event->service->client;
            $client = $clientManager->getClient($clientName);
            $response = new ServiceEventCallResponse();
            $this->serviceEventCall->status = ServiceEventCall::STATUS_BEING_DELIVERED;
            $this->serviceEventCall->attempts++;
            $this->serviceEventCall->save();
            $responseData = $client->request($this->serviceEventCall);
            $this->newResponse($responseData);
            $this->lastResponse = $response;
            $this->serviceEventCall->status = ServiceEventCall::STATUS_DELIVERED;
            $this->serviceEventCall->save();
        } catch (Throwable $e) {
            //NOT ALLOW RETRY HERE
            //RETRY SHOULD BASED ON THE ERROR CODE
            $this->failed($e);
        }
    }

    /**
     * Fail job
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        if ($exception instanceof \SoapFault) {
            $exception = $this->errorParser
                ->convertToServiceException($exception);
        }

        $status = ServiceEventCall::STATUS_SOFT_ERROR;

        if ($exception instanceof ServiceException) {
            switch ($exception->getCode()) {
                case ServiceException::NOT_ALLOW_RETRY:
                    $status = ServiceEventCall::STATUS_NOT_RETRYABLE;
                    break;
                case ServiceException::ENDPOINT_ERROR:
                case ServiceException::NETWORK_ERROR:
                    $status = ServiceEventCall::STATUS_HARD_ERROR;
                    break;
                default:
            }
        }

        $this->serviceEventCall->status = $status;
        $this->serviceEventCall->save();

        if (!isset($this->lastResponse)) {
            $this->newResponse($exception->getMessage());
        }

        event(new ServiceFailed($this->serviceEventCall, $exception));
    }

    /**
     * Create new response
     *
     * @param $data
     * @param $statusCode
     * @return ServiceEventCallResponse
     */
    private function newResponse($data, $statusCode = null): ServiceEventCallResponse
    {
        $response = new ServiceEventCallResponse();
        $response->setAttribute('parent_id', $this->serviceEventCall->id);
        $response->setAttribute('response', $this->normalizeData($data));
        $response->setAttribute('status_code', $statusCode);
        $response->save();

        return $response;
    }

    /**
     * @param $data
     * @return string
     */
    private function normalizeData($data): string
    {
        if (is_array($data)) {
            return json_encode($data);
        }

        if (is_object($data)) {
            // Unsure if casting this to an array is necessary
            return json_encode((array) $data);
        }

        return (string) $data;
    }
}
