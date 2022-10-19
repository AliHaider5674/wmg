<?php
namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Shipment shipment files
 *
 * Class ShipmentImportTest
 * @category WMG
 * @package  Tests\Feature\MES
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @group    service
 */
class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testBasicServiceOperation()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->get('api/1.0/service');
        $response->assertStatus(200);
        $response->assertJson([]);


        $sampleService = [
            'app_id' => 'mom',
            'name' => 'mom',
            'client' => 'mom',
            'event_rules' => [],
            'events' => ['item.shipped']
        ];

        //Add Service, success
        $response =$this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $sampleService);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
                'data' => [
                'app_id' => 'mom',
                'name' => 'mom',
                'client' => 'mom'
                ]
        ]);

        //Add again, update
        $copyRequest = $sampleService;
        $copyRequest['name'] = 'test';
        $response =$this->actingAs($user, 'api')
            ->json('POST', 'api/1.0/service', $copyRequest);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'app_id' => 'mom',
                'name' => 'test',
                'client' => 'mom'
            ]
        ]);

        $sampleResponse = $sampleService;
        $sampleResponse['events'] = [
            [
                'event' => 'item.shipped'
            ]
        ];
        $sampleResponse['name'] = 'test';
        $response = $this->actingAs($user, 'api')->get('api/1.0/service');
        $response->assertStatus(200);
        $response->assertJson([$sampleResponse]);

        $response = $this->actingAs($user, 'api')->delete('api/1.0/service/mom');
        $response->assertStatus(200);
        $response->assertJson(['status'=>'success']);
    }
}
