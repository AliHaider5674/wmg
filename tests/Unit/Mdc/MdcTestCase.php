<?php

namespace Tests\Unit\Mdc;

use App\Models\ServiceEventCall;
use App\Models\ServiceEvent;
use App\Models\Service;
use Tests\TestCase;
use App\Models\Service\Model\Serialize;

/**
 * Base MDC test case
 *
 * Class MdcTestCase
 * @category WMG
 * @package  Tests\Unit\Mdc
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
abstract class MdcTestCase extends TestCase
{
    protected $wsdl;

    public function setUp():void
    {
        parent::setUp();
        $this->wsdl = base_path() . '/tests/Unit/Mdc/soap.wsdl';
    }

    /**
     * Create new service event call
     * @param $event
     * @param \App\Models\Service\Model\Serialize $requestData
     * @return \App\Models\ServiceEventCall
     */
    protected function createServiceEventCall($event, Serialize $requestData)
    {
        $service = Service::factory()->create([
            'addition' => \GuzzleHttp\json_encode([
                'wsdl' => $this->wsdl,
                'username' => 'developer',
                'api_key' => 'password1'
            ])
        ]);

        $event = ServiceEvent::factory()->create([
            'parent_id' => $service->id,
            'event' => $event
        ]);

        return ServiceEventCall::factory()->create([
            'parent_id' => $event->id,
            'data' => \Opis\Closure\serialize($requestData)
        ]);
    }
}
