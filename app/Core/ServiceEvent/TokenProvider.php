<?php
namespace App\Core\ServiceEvent;

use App\Core\ServiceEvent\Clients\NetworkClients\ClientTokenInterface;
use App\Models\Service;
use App\Services\Token\TokenDbCache;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Token provider that renew token
 *
 * Class TokenProvider
 * @category WMG
 * @package  App\Core
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class TokenProvider
{
    protected $tokenCache;
    public function __construct(TokenDbCache $tokenCache)
    {
        $this->tokenCache = $tokenCache;
    }

    /**
     * @param \App\Models\Service                                 $service
     * @param \App\Core\ServiceEvent\Clients\NetworkClients\ClientTokenInterface $client
     * @return string
     */
    public function newToken(Service $service, ClientTokenInterface $client):string
    {
        $token = $client->newToken();
        if ($token instanceof AccessTokenInterface) {
            $token = json_encode($token->jsonSerialize());
        }
        $this->tokenCache->save($token, $service);
        return $token;
    }

    /**
     * @param \App\Models\Service                                                $service
     * @param \App\Core\ServiceEvent\Clients\NetworkClients\ClientTokenInterface $client
     * @return \League\OAuth2\Client\Token\AccessTokenInterface|mixed
     */
    public function getToken(Service $service, ClientTokenInterface $client)
    {
        $token = $this->tokenCache->get($service);
        $attemptToken = json_decode($token, true);
        if (!empty($attemptToken)) {
            $token = $attemptToken;
            if (array_key_exists('access_token', $attemptToken)) {
                $token = app()->make(AccessToken::class, ['options' => $attemptToken]);
            }
        }
        if ($token !== null) {
            $client->setToken($token);
            return $token;
        }


        $token = $client->newToken();
        if ($token instanceof AccessTokenInterface) {
            $this->tokenCache->save(json_encode($token->jsonSerialize()), $service);
            return $token;
        }
        $this->tokenCache->save($token, $service);
        return $token;
    }
}
