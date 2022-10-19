<?php

namespace App\Printful\Handler\IO;

use App\Core\Handlers\IO\IOInterface;
use App\Printful\Configurations\PrintfulConfig;
use App\Printful\Converter\Local\Order\ToOrderCreationParameters;
use Exception;
use Printful\PrintfulOrder;

/**
 * Class PrintfulOrderCreated
 *
 * IO Stream for handling PrintfulOrderCreated
 */
class PrintfulOrderCreated extends BasePrintfulStream
{
    /**
     * @var ToOrderCreationParameters
     */
    private $orderConverter;

    /**
     * @var PrintfulOrder
     */
    private $printfulOrderApi;

    /**
     * @var PrintfulConfig
     */
    private $printfulConfig;

    /**
     * PrintfulOrderCreated constructor.
     * @param PrintfulConfig $printfulConfig,
     * @param ToOrderCreationParameters $orderConverter
     * @param PrintfulOrder             $printfulOrderApi
     */
    public function __construct(
        PrintfulConfig $printfulConfig,
        ToOrderCreationParameters $orderConverter,
        PrintfulOrder $printfulOrderApi
    ) {
        $this->printfulConfig = $printfulConfig;
        $this->orderConverter = $orderConverter;
        $this->printfulOrderApi = $printfulOrderApi;
    }

    /**
     * Send data and then call callback
     *
     * @param      $data
     * @param null $callback
     * @throws Exception
     */
    public function send($data, $callback = null): void
    {
        $orderCreationParameters = $this->orderConverter->convert(
            $data[IOInterface::DATA_FIELD_RAW_ORDER]
        );

        $order = $this->printfulOrderApi->create(
            $orderCreationParameters,
            $this->printfulConfig->shouldConfirmOrder()
        );

        $data[IOInterface::DATA_FIELD_WAREHOUSE_ORDER] = $order;

        if ($callback) {
            $callback($data);
        }
    }
}
