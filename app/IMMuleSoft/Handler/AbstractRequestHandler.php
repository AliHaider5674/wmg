<?php

namespace App\IMMuleSoft\Handler;

use App\Core\Handlers\AbstractHandler;
use App\Core\Handlers\IO\IOInterface;
use App\Exceptions\MethodNotImplementedException;
use App\Exceptions\NoRecordException;
use App\IMMuleSoft\Handler\Processor\ProcessorInterface;
use App\IMMuleSoft\Models\ImMulesoftRequest;
use App\IMMuleSoft\Repositories\ImMulesoftRequestFilter;
use App\IMMuleSoft\Repositories\ImMulesoftRequestRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class AbstractRequestHandler
 * @package App\IMMuleSoft\Handler
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class AbstractRequestHandler extends AbstractHandler
{
    public const CONFIG_SIZE = 'size';
    public const DEFAULT_ORDER_LIMIT = 100;
    public const DEFAULT_MAX_ATTEMPTS = 3;

    protected string $resourceType = '';

    /**
     * @var array
     */
    protected array $statusesFilter = [
        ImMulesoftRequest::STATUS_RECEIVED
    ];

    private int $size = self::DEFAULT_ORDER_LIMIT;
    private int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS;

    protected ProcessorInterface $processor;
    protected ImMulesoftRequestFilter $requestFilter;
    protected ImMulesoftRequestRepository $requestRepository;


    /**
     * @inheritDoc
     */
    public function __construct(
        IOInterface                 $ioAdapter,
        Log                         $logger,
        ProcessorInterface          $processor,
        ImMulesoftRequestFilter     $requestFilter,
        ImMulesoftRequestRepository $requestRepository
    ) {
        parent::__construct($ioAdapter, $logger);
        $this->processor = $processor;
        $this->requestFilter = $requestFilter;
        $this->requestRepository = $requestRepository;
    }

    /**
     * Process configuration from args
     *
     * @param array $config
     *
     * @return void
     */
    protected function processConfig(array $config)
    {
        if (isset($config[self::CONFIG_SIZE])) {
            $this->setSize($config[self::CONFIG_SIZE]);
        }
    }

    /**
     * setSize
     * @param $size
     * @return $this
     */
    public function setSize($size): AbstractRequestHandler
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws NoRecordException
     */
    public function handle()
    {
        $requests = $this->getRequests();

        //return if no orders to drop
        if (!$requests->count()) {
            throw new NoRecordException(sprintf(
                "No %s are ready to be processed.",
                $this->resourceType
            ));
        }

        foreach ($requests as $request) {
            $this->processor->handle($request);
        }
    }

    /**
     * get request
     *
     * Get all requests by resource type
     *
     * @return Collection
     */
    protected function getRequests(): Collection
    {
        $this->requestFilter
            ->setSize($this->size)
            ->setAttempts($this->maxAttempts)
            ->setResourceType($this->resourceType);

        return $this->requestRepository->getRequestsByFilter($this->requestFilter);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws MethodNotImplementedException
     */
    protected function rollbackItem($item, ...$args): void
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }
}
