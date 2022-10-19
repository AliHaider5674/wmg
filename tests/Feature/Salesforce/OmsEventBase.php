<?php
namespace Tests\Feature\Salesforce;

use App\Salesforce\Clients\SalesforceOmsSDK;
use App\User;
use League\OAuth2\Client\Token\AccessToken;
use Tests\Feature\MES\MesTestCase;
use Mockery as M;

/**
 * Oms base test cases
 */
class OmsEventBase extends MesTestCase
{
    /** @var \App\Salesforce\Clients\SalesforceOmsSDK|\Mockery\LegacyMockInterface|\Mockery\MockInterface  */
    protected $omsSdk;
    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $service = [
            "app_id" => "salesforce",
            "name" => "salesforce",
            "client" => "salesforce.oms.restful",
            "events" => ["*"],
            "event_rules" => [],
            "app_url" => 'http://test/',
            "addition" => [
                "client_id" => "1111",
                "client_secret" => "secret",
                "redirect_url" => "redirect",
                'url_authorize' => 'http://oauth/authorize',
                'url_access_token' => 'http://oauth/token',
                'url_resource_owner_details' => ''
            ]
        ];
        $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);
        $this->omsSdk = M::mock(SalesforceOmsSDK::class);
        $this->omsSdk->shouldReceive('getToken')->andReturn(new AccessToken(['access_token' => '123']));
        $this->omsSdk->shouldReceive('newToken')->andReturn(new AccessToken(['access_token' => '123']));
        $this->app->instance(SalesforceOmsSDK::class, $this->omsSdk);
    }
}
