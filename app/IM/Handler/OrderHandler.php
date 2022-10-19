<?php declare(strict_types=1);

namespace App\IM\Handler;

use App\Core\Handlers\SingleOrderHandler;
use App\Core\Repositories\OrderLogRepository;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\IM\Configurations\ImConfig;
use App\IM\Handler\IO\ApiOrder;
use Illuminate\Support\Facades\Log;

/**
 * Class OrderHandler
 * @package App\IM\Handler
 */
class OrderHandler extends SingleOrderHandler
{
    /**
     * OrderHandler constructor.
     * @param ApiOrder               $ioAdapter
     * @param OrderRawMapperService    $orderDataMapperService
     * @param OrderRawConverterService $orderRawConverterService
     * @param Log                      $logger
     * @param ImConfig                 $config
     */
    public function __construct(
        ApiOrder $ioAdapter,
        OrderRawMapperService $orderDataMapperService,
        OrderRawConverterService $orderRawConverterService,
        OrderLogRepository $orderLogRepository,
        Log $logger,
        ImConfig $config
    ) {
        parent::__construct(
            $ioAdapter,
            $orderDataMapperService,
            $orderRawConverterService,
            $orderLogRepository,
            $logger,
            [self::CONFIG_SOURCE => $config->getSourceIds()]
        );
    }
}
