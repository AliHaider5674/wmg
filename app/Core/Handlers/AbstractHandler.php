<?php

namespace App\Core\Handlers;

use App\Core\Handlers\IO\IOInterface;
use App\Models\Data\Rollbackable;
use App\Models\Service\ModelBuilder\Parameter;
use App\Models\FailedParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use Illuminate\Support\Facades\Log;

/**
 * Warehouse handler abstract
 *
 * Class HandlerAbstract
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
abstract class AbstractHandler extends Rollbackable implements HandlerInterface
{
    /**
     * @var array
     */
    protected $processed = [];

    /**
     * @var IOInterface|null
     */
    protected $ioAdapter;

    /**
     * @var string
     */
    protected $name;

    protected $description;

    /**
     * @var Log
     */
    protected $logger;

    /**
     * AbstractHandler constructor.
     *
     * @param IOInterface|null $ioAdapter
     * @param Log                  $logger
     */
    public function __construct(IOInterface $ioAdapter, Log $logger)
    {
        $this->ioAdapter = $ioAdapter;
        $this->logger = $logger;
    }

    public function getName() : String
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription() : String
    {
        return $this->description ?? $this->name;
    }

    /**
     * @return mixed
     */
    abstract public function handle();

    /**
     * @return mixed
     */
    abstract public function validate();

    /**
     * Record failed
     * @param Parameter $parameter
     * @param string $errorMessage
     *
     * @return FailedParameter
     */
    protected function recordFailedParameter(
        Parameter $parameter,
        string $errorMessage = ''
    ): FailedParameter {
        $type = null;

        if ($parameter instanceof ShipmentLineChangeParameter) {
            $type = FailedParameter::TYPE_ACK;
        } elseif ($parameter instanceof ShipmentParameter) {
            $type = FailedParameter::TYPE_SHIPMENT;
        }

        $newFailedParameter = new FailedParameter();
        $newFailedParameter->fill([
            'type' => $type,
            'data' => $parameter->serialize(),
            'last_error' => $errorMessage,
            'attempts' => 0
        ]);

        $newFailedParameter->save();

        return $newFailedParameter;
    }
}
