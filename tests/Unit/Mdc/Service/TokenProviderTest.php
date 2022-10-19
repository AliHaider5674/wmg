<?php

namespace Tests\Unit\Mdc\Service;

use App\Mdc\Clients\SoapClient;
use App\Services\Token\TokenDbCache;
use App\Core\ServiceEvent\TokenProvider;
use App\Models\Service;
use Tests\Unit\Mdc\MdcTestCase;

/**
 * Test provider, for renewing token
 *
 * Class TokenProviderTest
 * @category WMG
 * @package  Tests\Unit\Mdc\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class TokenProviderTest extends MdcTestCase
{
    public function testNewToken()
    {
        $soapClientMock = $this->getMockBuilder(SoapClient::class)
                        ->disableOriginalConstructor()
                        ->addMethods(['login'])
                        ->getMock();
        $soapClientMock->method('login')
                       ->willReturn('TOKEN');
        $service = new Service();
        $service->setAddition([
            'wsdl' => $this->wsdl,
            'username' => 'developer',
            'api_key' => 'password1'
        ]);
        $tokenDbCacheMock = $this->getMockBuilder(TokenDbCache::class)->getMock();
        $tokenProvider = new TokenProvider($tokenDbCacheMock);
        $token = $tokenProvider->newToken($service, $soapClientMock);
        $this->assertNotEmpty($token);
    }
}
