<?php

namespace Tests\Feature\OrderAction;

use Tests\TestCase;
use App\User;

/**
 * Test Digital Shipment
 *
 * Class DigitalHandlerTest
 * @category WMG
 * @package  Tests\Feature\Warehouse
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @group orderAction
 * @testdox Test order action restful calls
 */
class OrderActionRestfulTest extends TestCase
{

    private $user;

    public function setUp():void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Create simple order action
     *
     * @testdox Create simple order action -> order action created
     * @return void
     */
    public function testApiCreateSimpleOrderAction()
    {
        $response = $this->createSampleOrderAction();
        $response->assertStatus(201)
            ->assertJson([
                'id' => 1,
                'order_id' => 1,
                'sales_channel' => 'brunomars',
                'action' => 'On Hold'
            ]);
    }

    /**
     * Test duplicate order action creation
     * @testdox Create duplicate order action -> response 500 error code
     *
     * @return void
     */
    public function testApiCreateDuplicateOrderAction()
    {
        $this->testApiCreateSimpleOrderAction();
        $response = $this->createSampleOrderAction();
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Internal error.'
            ]);
    }

    /**
     * Test create action with unknown field
     * @testdox Create action with unknown -> ignore unknown field and create action
     * @return void
     */
    public function testApiCreateOrderActionWithUnallowData()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/1.0/orderaction', [
            'order_id' => 1,
            'sales_channel' => 'brunomars',
            'action' => 'On Hold',
            'data' => [],
            'notexist' => 1
            ]);
        $response->assertStatus(201)
            ->assertJson([
                'id' => 1,
                'order_id' => 1,
                'sales_channel' => 'brunomars',
                'action' => 'On Hold'
            ]);
    }

    /**
     * Update simple order action
     * @testdox  Update simple order action -> order action got updated
     * @return void
     */
    public function testApiUpdateSimpleOrderAction()
    {
         $this->createSampleOrderAction();

        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/1.0/orderaction', [
            'id' => 1,
            'order_id' => 2,
            'sales_channel' => 'aliceinchain',
            'action' => 'On Hold'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => 1,
                'order_id' => 2,
                'sales_channel' => 'aliceinchain',
                'action' => 'On Hold'
            ]);
    }

    /**
     * Update order action that not exist
     * @testdox Update non-exist order action -> response 400 error
     * @return void
     */
    public function testApiUpdateWithNonExistId()
    {
        $response = $this->actingAs($this->user, 'api')
            ->json('POST', '/api/1.0/orderaction', [
            'id' => 1,
            'order_id' => 2,
            'sales_channel' => 'aliceinchain',
            'action' => 'On Hold'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Order action do not exist.'
            ]);
    }

    /**
     * Test api delete order action
     * @testdox Delete order action -> order action got deleted
     * @return void
     */
    public function testApiDeleteOrderAction()
    {
        $this->createSampleOrderAction();
        $response = $this->actingAs($this->user, 'api')
            ->delete('/api/1.0/orderaction/1');
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order Action has been deleted.'
            ]);
    }

    /**
     * Test api delete non-exist order action
     * @testdox Delete non-exist order action -> response 400
     * @return void
     */
    public function testApiDeleteNonExistOrderAction()
    {
        $response = $this->actingAs($this->user, 'api')
            ->delete('/api/1.0/orderaction/1');
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Order Action do not exist.'
            ]);
    }

    /**
     * Test get list of actions
     * @testdox Get list of order actions -> return full list
     * @return void
     */
    public function testApiGetListOrderAction()
    {
        $this->createSampleOrderAction();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/1.0/orderaction');
        $response->assertStatus(200)
            ->assertJson([[
                'id' => 1,
                'order_id' => 1,
                'sales_channel' => 'brunomars',
                'action' => 'On Hold',
                'setting' => null,
                'exec_data' => null
            ]]);
    }


    private function createSampleOrderAction()
    {
        return $this->actingAs($this->user, 'api')
            ->json('POST', '/api/1.0/orderaction', [
            'order_id' => 1,
            'sales_channel' => 'brunomars',
            'action' => 'On Hold'
            ]);
    }
}
