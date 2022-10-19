<?php

namespace App\MES\Handler\IO;

use App\Services\FileSystemService;
use Symfony\Component\Finder\Finder;
use FileDataConverter\File\Flat;
use App\MES\Handler\IO\Shipment\Tracker;
use App\MES\MesShipment;

/**
 * Handle mes flat shipment file import
 *
 * Class FlatShipment
 * @category WMG
 * @package  App\MES\Handler\IO
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FlatShipment extends ImportAbstract
{
    public const FILE_PREFIX = 'DESADV_WMGUSM';

    /**
     * @var string
     */
    protected $filenamePrefix = self::FILE_PREFIX;

    /**
     * @var Finder
     */
    protected $fileFinder;

    /**
     * @var Tracker
     */
    protected $shipmentTracker;

    /**
     * @var array
     */
    protected $processedFiles;

    /**
     * @var array
     */
    private $carrierMap;

    /**
     * FlatShipment constructor.
     * @param Flat              $fileIO
     * @param array             $config
     * @param FileSystemService $fileSystemService
     * @param Tracker           $shipmentTracker
     */
    public function __construct(
        Flat $fileIO,
        array $config,
        FileSystemService $fileSystemService,
        Tracker $shipmentTracker
    ) {
        parent::__construct($fileIO, $config, $fileSystemService);
        $this->shipmentTracker = $shipmentTracker;
    }

    /**
     * Start reading shipment file
     *
     * @param string $file
     * @param null $callback
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed|void
     */
    protected function startReadFile(string $file, $callback = null)
    {
        $this->carrierMap = [];
        $this->shipmentTracker->reset();
    }

    /**
     * Read file line
     *
     * @param string $file
     * @param array  $data
     * @param string $section
     * @param null   $callback
     *
     * @return mixed|void
     * @throws \App\Exceptions\RecordExistException
     */
    protected function readFileLine(
        string $file,
        array $data,
        string $section,
        callable $callback = null
    ) {
        if ($section === 'order') {
            $this->shipmentTracker->addOrder($data);
            $this->carrierMap[$data['order_number']] = [
                'carrier_number' => $data['carrier_number'],
                'carrier_name' => $data['carrier_name']
            ];
        } elseif ($section === 'line') {
            $data['carrier_number'] = $this->carrierMap[$data['order_number']]['carrier_number'];
            $data['carrier_name'] = $this->carrierMap[$data['order_number']]['carrier_name'];
            $this->shipmentTracker->addItem($data);
        }
    }


    /**
     * Finish reading a file
     *
     * @param string        $file
     * @param callable|null $callback
     *
     * @return mixed|void
     */
    protected function finishReadFile(string $file, callable $callback = null)
    {
        $shipment = new MesShipment();
        $shipment->fill([
            'file' => pathinfo($file, PATHINFO_BASENAME),
            'status' => MesShipment::STATUS_PROCESSING,
            'order_count' => $this->shipmentTracker->count()
        ]);
        $shipment->save();
        $this->recordProcessed($shipment);

        /**@var \App\Models\Service\Model\Shipment $shipmentModel*/
        foreach ($this->shipmentTracker->getIterator() as $shipmentModel) {
            $callback($shipmentModel);
        }
    }

    /**
     * @param       $item
     * @param mixed ...$args
     */
    protected function rollbackItem($item, ...$args): void
    {
        if ($item instanceof MesShipment) {
            $item->status = MesShipment::STATUS_ERROR;
            $item->save();
        }
    }
}
