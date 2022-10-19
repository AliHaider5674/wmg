<?php

namespace App\Salesforce\Clients;

use Exception;

/**
 * Class Config
 * @package App\Salesforce\Clients
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Config
{
    protected string $username;
    protected string $password;
    protected string $organizationId;
    protected string $grantType;
    protected string $authUrl;
    protected string $authScope;
    protected string $baseUrl;


    /**
     * setConfig
     * @param array $config
     * @throws Exception
     */
    public function setConfig(array $config)
    {
        if (!isset($config['username'])
            || !isset($config['password'])
            || !isset($config['organization_id'])
            || !isset($config['grant_type'])
            || !isset($config['auth_url'])
            || !isset($config['auth_scope'])
            || !isset($config['base_url'])
        ) {
            throw new Exception('Missing API credentials');
        }

        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->organizationId = $config['organization_id'];
        $this->grantType = $config['grant_type'];
        $this->authUrl = $config['auth_url'];
        $this->authScope = $config['auth_scope'];
        $this->baseUrl = $config['base_url'];
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Config
     */
    public function setUsername(string $username): Config
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Config
     */
    public function setPassword(string $password): Config
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    /**
     * @param string $organizationId
     * @return Config
     */
    public function setOrganizationId(string $organizationId): Config
    {
        $this->organizationId = $organizationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrantType(): string
    {
        return $this->grantType;
    }

    /**
     * @param string $grantType
     * @return Config
     */
    public function setGrantType(string $grantType): Config
    {
        $this->grantType = $grantType;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    /**
     * @param string $authUrl
     * @return Config
     */
    public function setAuthUrl(string $authUrl): Config
    {
        $this->authUrl = $authUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthScope(): string
    {
        return $this->authScope;
    }

    /**
     * @param string $authScope
     * @return Config
     */
    public function setAuthScope(string $authScope): Config
    {
        $this->authScope = $authScope;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     * @return Config
     */
    public function setBaseUrl(string $baseUrl): Config
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }
}
