<?php declare(strict_types=1);

namespace App\Printful\Configurations;

/**
 * Class PrintfulConfig
 * @package App\Printful\Configurations
 */
class PrintfulConfig
{
    /**
     * @var string|null
     */
    private $apiUrl;

    /**
     * @var bool
     */
    private $confirmOrder;

    /**
     * @var array|null
     */
    private $enabledWebhooks;

    /**
     * @var array|null
     */
    private $carrierExpMap;

    /**
     * @var string
     */
    private $webhookKey;
    /**
     * @var array|null
     */
    private $customCountryStateMap;

    /**
     * PrintfulConfig constructor.
     * @param string|null $apiUrl
     * @param bool $confirmOrder
     * @param array|null $enabledWebhooks
     * @param array|null $carrierExpMap
     * @param string|null $webhookKey
     * @param array|null $customCountryStateMap
     */
    public function __construct(
        ?string $apiUrl,
        bool $confirmOrder,
        ?array $enabledWebhooks,
        ?array $carrierExpMap,
        ?string $webhookKey,
        ?array $customCountryStateMap
    ) {
        $this->apiUrl = $apiUrl;
        $this->confirmOrder = $confirmOrder;
        $this->enabledWebhooks = $enabledWebhooks;
        $this->carrierExpMap = $carrierExpMap;
        $this->webhookKey = $webhookKey;
        $this->customCountryStateMap = $customCountryStateMap;
    }

    /**
     * Printful API URL
     *
     * @return string|null
     */
    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    /**
     * Should create a confirmed order (true) or draft (false)
     *
     * @return bool|null
     */
    public function shouldConfirmOrder(): ?bool
    {
        return $this->confirmOrder;
    }

    /**
     * Get Enabled Webhook Events
     *
     * @return array|null
     */
    public function getEnabledWebhooks(): ?array
    {
        return $this->enabledWebhooks;
    }

    /**
     * Get Carrier Regexp Map
     *
     * @return array|null
     */
    public function getCarrierExpMap(): ?array
    {
        return $this->carrierExpMap;
    }

    /**
     * Get the webhook key for authenticating webhook requests
     *
     * @return string|null
     */
    public function getWebhookKey(): ?string
    {
        return $this->webhookKey;
    }

    /**
     * getCustomCountryStateMap
     *
     * Custom mapping to map Magento Country States with Printful Country States that
     * dont map automatically
     *
     * @return array|null
     */
    public function getCustomCountryStateMap(): ?array
    {
        return $this->customCountryStateMap;
    }
}
