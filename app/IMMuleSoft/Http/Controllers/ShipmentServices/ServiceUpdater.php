<?php

namespace App\IMMuleSoft\Http\Controllers\ShipmentServices;

use Illuminate\Support\Facades\DB;
use App\IMMuleSoft\Models\ImMulesoftShippingCarrierService;
use \App\IMMuleSoft\Models\ImMulesoftShippingServiceMapper;

/**
 * Class ServiceUpdater
 * @package App\IMMuleSoft\Http\Controllers\ShipmentServices
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ServiceUpdater
{
    private ImMulesoftShippingCarrierService $shippingCarrierService;
    private ImMulesoftShippingServiceMapper $serviceMapping;

    public function __construct(
        ImMulesoftShippingCarrierService $shippingCarrierService,
        ImMulesoftShippingServiceMapper $serviceMapping
    ) {
        $this->shippingCarrierService = $shippingCarrierService;
        $this->serviceMapping = $serviceMapping;
    }

    /**
     * update
     * @param array $carrierServices
     * @param array $serviceMapping
     */
    public function update(array $carrierServices, array $serviceMapping)
    {
        DB::transaction(function () use ($carrierServices, $serviceMapping) {
            if ($carrierServices) {
                $this->shippingCarrierService->newQuery()->delete();
                $this->shippingCarrierService->newQuery()->insert($carrierServices);
            }
            if (!empty($serviceMapping)) {
                $this->serviceMapping->newQuery()->delete();
                $this->serviceMapping->newQuery()->insert($serviceMapping);
            }
        }, 2);
    }
}
