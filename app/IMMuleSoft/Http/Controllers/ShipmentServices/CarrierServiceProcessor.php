<?php

namespace App\IMMuleSoft\Http\Controllers\ShipmentServices;

use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Class CarrierServiceProcessor
 * @package App\IMMuleSoft\Http\Controllers\ShipmentServices
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class CarrierServiceProcessor
{
    const CARRIER_SERVICE_NUMBER_OF_COLUMNS = 4;

    private array $carrierServiceHeader = array('carrier_code', 'carrier_name', 'service_code', 'service_name');

    /**
     * process
     * @param string $carrierServicePath
     * @return array
     * @throws Exception
     */
    public function process(string $carrierServicePath): array
    {
        $row = 1;
        $lines = array();

        $handle = fopen(Storage::path($carrierServicePath), "r");
        if ($handle !== false) {
            while (($data = fgetcsv($handle, 1000)) !== false) {
                if (self::CARRIER_SERVICE_NUMBER_OF_COLUMNS !== count($data)) {
                    throw new Exception("Carrier Service. Incorrect number of columns");
                }

                if (1 == $row) {
                    if ($data !== $this->carrierServiceHeader) {
                        throw new Exception("Carrier Service header row does not match expected values");
                    }
                    $row++;
                    continue;
                }

                $line = array();
                $line['carrier_code'] = $data[0];
                $line['carrier_name'] = $data[1];
                $line['service_code'] = $data[2];
                $line['service_name'] = $data[3];

                $lines[] = $line;
                $row++;
            }
            fclose($handle);
        }
        return $lines;
    }
}
