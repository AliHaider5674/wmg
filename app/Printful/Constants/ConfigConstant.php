<?php declare(strict_types=1);

namespace App\Printful\Constants;

/**
 * Class ConfigConstant
 * @package App\Printful\Constants
 */
class ConfigConstant
{
    /**
     * Printful Carrier Map
     */
    public const CARRIER_MAP = 'printful.carrier.map';

    /**
     * Printful API Key
     */
    public const API_KEY = 'printful.api.key';

    /**
     * Printful API Url
     */
    public const API_URL = 'printful.api.url';

    /**
     * Whitelisted API IPs
     */
    public const WHITELISTED_IPS = 'printful.api.whitelisted-ips';

    /**
     * Should confirm order as opposed to create it as a draft
     */
    public const SHOULD_CONFIRM_ORDER = 'printful.order.confirm';

    /**
     * Enabled Webhooks Config Key
     */
    public const ENABLED_WEBHOOKS = 'printful.api.enabled-webhooks';

    /**
     * Printful Webhook Route Name
     */
    public const WEBHOOK_ROUTE_NAME = 'printful-webhook';

    /**
     * Printful Webhook Route Key Parameter
     */
    public const WEBHOOK_KEY = 'printful.webhook.key';

    /**
     * Printful Custom  Country State Map
     * Map Magento Country States to  Printful Country State which dont map automatically
     */
    public const CUSTOM_COUNTRY_STATE_MAP = 'printful.custom.country.state.map';
}
