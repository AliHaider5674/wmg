<?php
namespace App\Core\ServiceEvent\Clients\Traits;

use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Trait for restful OAUTH calls
 */
trait RestOAuth
{
    private GenericProvider $provider;
    private array $oAuthConfig;
    private AccessTokenInterface $token;
    protected function configOAuth(array $oAuthConfig, array $existToken = null)
    {
        $this->oAuthConfig = $oAuthConfig;
        $this->provider = new GenericProvider([
            'clientId' => $oAuthConfig['client_id'] ?? null,
            'clientSecret' => $oAuthConfig['client_secret'] ?? null,
            'redirectUri' => $oAuthConfig['redirect_url'] ?? null,
            'urlAuthorize' => $oAuthConfig['url_authorize'] ?? $oAuthConfig['url'] . '/authorize',
            'urlAccessToken' => $oAuthConfig['url_access_token'] ?? $oAuthConfig['url'] . '/token',
            'urlResourceOwnerDetails' => $oAuthConfig['url_resource_owner_details']
                ?? $oAuthConfig['url'] . '/resource',
        ]);
        if ($existToken) {
            $this->token = new AccessToken($existToken);
        }
    }

    public function getToken()
    {
        if (!isset($this->token)) {
            if (!isset($this->oAuthConfig['username']) || !isset($this->oAuthConfig['password'])) {
                throw new InvalidGrantException('Grant type isn\'t supported');
            }
            $this->token = $this->provider->getAccessToken('password', [
                'username' => $this->oAuthConfig['username'],
                'password' => $this->oAuthConfig['password']
            ]);
        }
        if ($this->token->getExpires() && $this->token->hasExpired()) {
            $this->token = $this->provider->getAccessToken(
                'refresh_token',
                ['refresh_token' => $this->token->getToken()]
            );
        }
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        if (is_array($token)) {
            $this->token = new AccessToken($token);
        } elseif (is_string($token)) {
            $this->token = new AccessToken([
                'access_token' => $token
            ]);
        }
        $this->token = $token;
    }

    public function invalidToken()
    {
        if (isset($this->token)) {
            unset($this->token);
        }
    }
}
