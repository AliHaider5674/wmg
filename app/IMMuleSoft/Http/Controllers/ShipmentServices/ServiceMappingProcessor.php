<?php

namespace App\IMMuleSoft\Http\Controllers\ShipmentServices;

use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Class ServiceMappingProcessor
 * @package App\IMMuleSoft\Http\Controllers\ShipmentServices
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ServiceMappingProcessor
{
    const SERVICE_MAPPING_NUMBER_OF_COLUMNS = 8;

    private array $shippingMappingHeader = array(
        'country_code',
        'condition_name',
        'delivery_type',
        'condition_from_value',
        'condition_to_value',
        'carrier_code',
        'service_code',
        'dispatch_offset'
    );

    /**
     * process
     * @param string $serviceMappingPath
     * @return array
     */
    public function process(string $serviceMappingPath): array
    {
        $row = 1;
        $lines = array();

        $handle = fopen(Storage::path($serviceMappingPath), "r");

        if ($handle !== false) {
            while (($data = fgetcsv($handle, 1000)) !== false) {
                if (self::SERVICE_MAPPING_NUMBER_OF_COLUMNS !== count($data)) {
                    throw new Exception("Service Mapping. Incorrect number of columns");
                }

                if (1 == $row) {
                    if ($data !== $this->shippingMappingHeader) {
                        throw new Exception("Service Mapping header row does not match expected values");
                    }
                    $row++;
                    continue;
                }

                $line = array();
                $line['country_code'] = $data[0];
                $line['condition_name'] = $data[1];
                $line['delivery_type'] = $data[2];
                $line['condition_from_value'] = $data[3];
                $line['condition_to_value'] = $data[4];
                $line['carrier_code'] = $data[5];
                $line['service_code'] = $data[6];
                $line['dispatch_offset'] = (int) $data[7];

                $lines[] = $line;
                $row++;
            }
            fclose($handle);
        }

        return $lines;
    }
}
