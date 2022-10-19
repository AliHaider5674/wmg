<?php

namespace App\Shopify\Http\Middleware;

use App\Core\Repositories\ServiceRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Closure;

/**
 * Class Authentication
 * @package App\Shopify\Http\Middleware
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ShopifyRequestAuth
{
    private string $signatureHeader;
    private ServiceRepository $serviceRepository;

    const HASH_ALGO = 'sha256';

    /**
     * @param ServiceRepository $serviceRepository
     * @param string $signatureHeader
     */
    public function __construct(
        ServiceRepository $serviceRepository,
        string $signatureHeader = 'HTTP_X_SHOPIFY_HMAC_SHA256'
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->signatureHeader = $signatureHeader;
    }


    /**
     * getSignature
     * @return array|string|null
     */
    protected function getSignature(REQUEST $request)
    {
        return $request->server($this->signatureHeader)  ?? '';
    }

    public function handle(Request $request, Closure $next, ...$guards)
    {
        $signature = $this->getSignature($request);
        $raw = $this->getRawData($request);
        $appId = $request->route('shop');
        if (empty($appId)) {
            throw new AuthenticationException('Unauthorized', $guards, null);
        }

        $service = $this->serviceRepository->getByAppId($appId);
        if (empty($service)) {
            throw new AuthenticationException('Unauthorized', $guards, null);
        }
        $addition = $service->getAddition();
        $secret = $addition['secret'] ?? '';
        if (!$this->isAuthenticated($signature, $secret, $raw)) {
            throw new AuthenticationException('Unauthorized', $guards, null);
        }
        return $next($request);
    }

    /**
     * Check if the current request signed
     * @param null $signature
     * @param null $rawData
     * @return bool
     */
    public function isAuthenticated($signature, $secret, $rawData): bool
    {
        $calculatedSignature = base64_encode(hash_hmac(self::HASH_ALGO, $rawData, $secret, true));
        return hash_equals($signature, $calculatedSignature);
    }

    /**
     * getRawData
     * @param Request $request
     * @return string
     */
    protected function getRawData(Request $request) : string
    {
        $params = array();

        foreach ($request->all() as $key => $value) {
            $params[] = $key."=".$value;
        }

        return join('&', $params);
    }
}
