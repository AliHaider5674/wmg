<?php declare(strict_types=1);

namespace App\IM\Configurations;

/**
 * Class ImConfig
 * @package App\IM\Configurations
 */
class ImConfig
{
    /**
     * @var string|null
     */
    private $baseApiEndpoint;

    /**
     * @var string|null
     */
    private $apiUsername;

    /**
     * @var string|null
     */
    private $apiPassword;

    /**
     * @var array
     */
    private $sourceIds;

    /**
     * ImConfig constructor.
     * @param string|null $baseApiEndpoint
     * @param string|null $apiUsername
     * @param string|null $apiPassword
     * @param array  $sourceIds
     */
    public function __construct(
        string $baseApiEndpoint = null,
        string $apiUsername = null,
        string $apiPassword = null,
        array $sourceIds = []
    ) {
        $this->baseApiEndpoint = $baseApiEndpoint;
        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;
        $this->sourceIds = $sourceIds;
    }

    /**
     * @return string
     */
    public function getApiPassword(): string
    {
        return $this->apiPassword;
    }

    /**
     * @return string
     */
    public function getApiUsername(): string
    {
        return $this->apiUsername;
    }

    /**
     * @return string
     */
    public function getBaseApiEndpoint(): string
    {
        return $this->baseApiEndpoint;
    }

    /**
     * @return array
     */
    public function getSourceIds(): array
    {
        return $this->sourceIds;
    }
}
