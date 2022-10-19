<?php
namespace Tests\Unit\Shopify\Middleware;

use App\Shopify\Http\Middleware\ShopifyRequestAuth;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

/**
 * Class ShopifyAuthenticationTest
 * @package Tests\Unit\Shopify\Middleware
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ShopifyAuthenticationTest extends TestCase
{
    const SHOPIFY_SECRET_SUCCESS = 'hu4wz1xgbtdruzx8d2fmvj9nchit1124';
    const SHOPIFY_SECRET_FAIL = 'v5qod2ey3x5q46ubqlsnfuchn2nsw7lh';
    const HASH_ALGO = 'sha256';
    private ShopifyRequestAuth $auth;
    public function setUp(): void
    {
        parent::setUp();
        $this->auth = $this->getMockBuilder(ShopifyRequestAuth::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRawData'])
            ->getMock();

        $testRawData = $this->getTestRawData();
        $this->auth->method('getRawData')->willReturn($testRawData);
    }

    /**
     * testShopifyAuthenticationSuccess
     */
    public function testShopifyAuthenticationSuccess()
    {
        $rawData = $this->getTestRawData();
        $signature = $this->getTestSignature($this->getTestRawData(), self::SHOPIFY_SECRET_SUCCESS);
        $isAuthenticated = $this->auth->isAuthenticated($signature, self::SHOPIFY_SECRET_SUCCESS, $rawData);
        $this->assertEquals(true, $isAuthenticated);
    }

    /**
     * testShopifyAuthenticationFailure
     */
    public function testShopifyAuthenticationFailure()
    {
        $rawData = $this->getTestRawData();
        $signature = $this->getTestSignature($this->getTestRawData(), self::SHOPIFY_SECRET_SUCCESS);
        $isAuthenticated = $this->auth->isAuthenticated($signature, self::SHOPIFY_SECRET_FAIL, $rawData);
        $this->assertEquals(false, $isAuthenticated);
    }


    /**
     * getTestSignature
     * @param string $rawData
     * @param string $secret
     * @return string
     */
    public function getTestSignature(string $rawData, string $secret): string
    {
        return base64_encode(hash_hmac(self::HASH_ALGO, $rawData, $secret, true));
    }

    /**
     * getTestRawData
     * @return string
     */
    public function getTestRawData(): string
    {
        $params = array();

        $index = 0;
        while ($index <= 5) {
            $params[] = sprintf('key%d=value%d', $index, $index);
            $index++;
        }
        return join('&', $params);
    }
}
