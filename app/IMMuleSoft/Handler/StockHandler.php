<?php

namespace App\IMMuleSoft\Handler;

use App\IMMuleSoft\Constants\ResourceConstant;
use App\IMMuleSoft\Handler\IO\Dummy;
use App\IMMuleSoft\Handler\Processor\Stock;
use App\IMMuleSoft\Repositories\ImMulesoftRequestFilter;
use App\IMMuleSoft\Repositories\ImMulesoftRequestRepository;
use Illuminate\Support\Facades\Log;

/**
 * Class StockHandler
 * @package App\IMMuleSoft\Handler
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class StockHandler extends AbstractRequestHandler
{
    protected string $resourceType = ResourceConstant::RESOURCE_TYPE_STOCK_LEVEL;

    /**
     * @inheritDoc
     */
    public function __construct(
        Dummy $ioAdapter,
        Log   $logger,
        Stock $processor,
        ImMulesoftRequestFilter     $requestFilter,
        ImMulesoftRequestRepository $requestRepository,
        array $config = []
    ) {
        parent::__construct(
            $ioAdapter,
            $logger,
            $processor,
            $requestFilter,
            $requestRepository
        );
        $this->processConfig($config);
    }
}
