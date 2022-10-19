<?php

namespace App\IMMuleSoft\Http\Controllers;

use App\Http\Controllers\Controller;
use App\IMMuleSoft\Http\Controllers\ShipmentServices\CarrierServiceProcessor;
use App\IMMuleSoft\Http\Controllers\ShipmentServices\ServiceMappingProcessor;
use App\IMMuleSoft\Http\Controllers\ShipmentServices\ServiceUpdater;
use App\IMMuleSoft\Models\ImMulesoftShippingCarrierService;
use App\IMMuleSoft\Models\ImMulesoftShippingServiceMapper;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class ShipmentServicesController
 * @package App\IMMuleSoft\Http\Controllers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ShipmentServicesController extends Controller
{
    const FILE_SERVICE_MAPPING_REQUEST_ID = 'service_mapping';
    const FILE_CARRIER_SERVICE_REQUEST_ID = 'carrier_service';

    private ImMulesoftShippingCarrierService $shippingCarrierService;
    private ImMulesoftShippingServiceMapper $shippingMapping;
    private CarrierServiceProcessor $carrierServiceProcessor;
    private ServiceMappingProcessor $serviceMappingProcessor;
    private ServiceUpdater $serviceUpdater;


    public function __construct(
        CarrierServiceProcessor $carrierServiceProcessor,
        ServiceMappingProcessor $serviceMappingProcessor,
        ImMulesoftShippingCarrierService $shippingCarrierService,
        ImMulesoftShippingServiceMapper $shippingMapping,
        ServiceUpdater $serviceUpdater
    ) {
        $this->carrierServiceProcessor = $carrierServiceProcessor;
        $this->serviceMappingProcessor = $serviceMappingProcessor;
        $this->shippingCarrierService = $shippingCarrierService;
        $this->shippingMapping = $shippingMapping;
        $this->serviceUpdater = $serviceUpdater;
    }

    /**
     * isFilesUpload
     * @param Request $request
     * @param array $requiredFiles
     * @return array
     */
    protected function isFilesUpload(Request $request, array $requiredFiles) : array
    {
        $result = array();
        $result['status'] = true;
        $result['message'] = 'The following files are missing: ';

        foreach ($requiredFiles as $file) {
            if (!$request->has($file)) {
                $result['status'] = false;
                $result['message'] = $result['message'] . "$file. ";
            }
        }
        return $result;
    }

    /**
     * __invoke
     * @param Request $request
     * @return JsonResponse|void
     * @throws FileNotFoundException
     */
    public function __invoke(Request $request)
    {
        $result = $this->isFilesUpload($request, [
            self::FILE_SERVICE_MAPPING_REQUEST_ID,
            self::FILE_CARRIER_SERVICE_REQUEST_ID
        ]);

        if (false === $result['status']) {
            return new JsonResponse(
                [
                    [
                        'message' => $result['message']
                    ],
                ],
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        $carrierServices = array();
        if ($request->has(self::FILE_CARRIER_SERVICE_REQUEST_ID)) {
            $carrierServices = $this->processCarrierServices($request);
        }

        $serviceMapping = array();
        if ($request->has(self::FILE_SERVICE_MAPPING_REQUEST_ID)) {
            $serviceMapping = $this->processServiceMapping($request);
        }

        if (!empty($carrierServices) || !empty($serviceMapping)) {
            $this->serviceUpdater->update($carrierServices, $serviceMapping);
        }
    }

    /**
     * processCarrierServices
     * @param Request $request
     * @return array
     * @throws FileNotFoundException
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    private function processCarrierServices(Request $request): array
    {
        $carrierServicePath = $request
            ->file(self::FILE_CARRIER_SERVICE_REQUEST_ID)
            ->store(self::FILE_CARRIER_SERVICE_REQUEST_ID);

        if (empty($carrierServicePath)) {
            throw new FileNotFoundException('Unable to process Carrier Service file');
        }

        return $this->carrierServiceProcessor->process($carrierServicePath);
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    private function processServiceMapping($request): array
    {
        $serviceMappingPath = $request
            ->file(self::FILE_SERVICE_MAPPING_REQUEST_ID)
            ->store(self::FILE_SERVICE_MAPPING_REQUEST_ID);

        if (empty($serviceMappingPath)) {
            throw new FileNotFoundException('Unable to process Service Mapping file');
        }

        return $this->serviceMappingProcessor->process($serviceMappingPath);
    }
}
