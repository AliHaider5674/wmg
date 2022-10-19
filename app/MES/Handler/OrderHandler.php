<?php declare(strict_types=1);

namespace App\MES\Handler;

use App\Core\Handlers\BatchOrderHandler;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\MES\Handler\IO\FlatOrder;
use Illuminate\Support\Facades\Log;

/**
 * Class OrderHandler
 * @package App\MES\Handler
 */
class OrderHandler extends BatchOrderHandler
{
    /**
     * AbstractOrderHandler constructor.
     *
     * @param array                    $config    Handler configuration
     * @param FlatOrder                $ioAdapter IO Adapter
     * @param OrderRawMapperService    $orderDataMapperService
     * @param OrderRawConverterService $orderRawConverterService
     * @param Log                      $logger
     */
    public function __construct(
        FlatOrder $ioAdapter,
        OrderRawMapperService $orderDataMapperService,
        OrderRawConverterService $orderRawConverterService,
        Log $logger,
        array $config = []
    ) {
        parent::__construct(
            $ioAdapter,
            $orderDataMapperService,
            $orderRawConverterService,
            $logger,
            $config
        );
    }
}
