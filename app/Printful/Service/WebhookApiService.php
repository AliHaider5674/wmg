<?php declare(strict_types=1);

namespace App\Printful\Service;

use WMGCore\Services\ConfigService;
use App\Printful\Constants\ConfigConstant;
use App\Printful\Exceptions\InvalidWebhookUrlException;
use Printful\PrintfulWebhook;
use Printful\Structures\Webhook\WebhooksInfoItem;

/**
 * Class WebhookApiService
 * @package App\Printful\Service
 */
class WebhookApiService
{
    /**
     * Format for invalid URL message
     */
    protected const INVALID_URL_MESSAGE_FORMAT = <<<MSG
Error, url %s is not valid. Please provide a valid URL with the scheme (eg. https://website.com/path/)';
MSG;

    /**
     * Format for local host as app URL
     */
    protected const LOCAL_HOST_MESSAGE = <<<MSG
You cannot register webhooks to localhost. To receive webhooks locally try using a tunnel service such as ngrok.
MSG;

    /**
     * List of local hosts
     */
    protected const LOCAL_HOSTS = ['localhost', '127.0.0.1'];

    /**
     * @var PrintfulWebhook
     */
    protected $printfulWebhook;

    /**
     * @var ConfigService
     */
    protected $configService;

    /**
     * @var string
     */
    protected $defaultAppUrl;

    /**
     * @var WebhookKeyGenerator $keyGenerator
     */
    protected $keyGenerator;

    /**
     * WebhookApiService constructor.
     * @param PrintfulWebhook     $printfulWebhook
     * @param ConfigService       $configService
     * @param WebhookKeyGenerator $keyGenerator
     * @param string              $defaultAppUrl
     */
    public function __construct(
        PrintfulWebhook $printfulWebhook,
        ConfigService $configService,
        WebhookKeyGenerator $keyGenerator,
        string $defaultAppUrl
    ) {
        $this->printfulWebhook = $printfulWebhook;
        $this->configService = $configService;
        $this->keyGenerator = $keyGenerator;
        $this->defaultAppUrl = $defaultAppUrl;
    }

    /**
     * Register webhooks for a specific key or App URL
     *
     * If the key is null, we will use the current configuration key or generate
     * a new one and save that key into the configuration
     *
     * @param array            $events
     * @param string|null|bool $key Key or false if one should be forced to be
     *                              generated
     * @param string|null      $appUrl
     * @return WebhooksInfoItem
     * @throws InvalidWebhookUrlException
     */
    public function registerWebhooks(
        array $events,
        &$key = null,
        string $appUrl = null
    ): WebhooksInfoItem {
        $key = $this->updateOrGetKey($key);
        $appUrl = $appUrl ?? $this->defaultAppUrl;

        if (!$this->validateUrl($appUrl)) {
            throw new InvalidWebhookUrlException(
                sprintf(self::INVALID_URL_MESSAGE_FORMAT, $appUrl)
            );
        }

        if ($this->urlIsLocal($appUrl)) {
            throw new InvalidWebhookUrlException(self::LOCAL_HOST_MESSAGE);
        }

        $webhookUrl = $this->getWebhookUrl($appUrl, $key);

        return $this->printfulWebhook->registerWebhooks($webhookUrl, $events);
    }

    /**
     * Check whether URL is valid
     *
     * @param string $url
     * @return bool
     */
    protected function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function urlIsLocal(string $url): bool
    {
        return in_array(
            parse_url($url, PHP_URL_HOST),
            self::LOCAL_HOSTS,
            true
        );
    }

    /**
     * If a key is provided, update the configuration for the key to the
     * provided key. Otherwise, get the key from the configuration or generate
     * and add a key to the configuration
     *
     * @param string|null $key
     * @return string
     */
    public function updateOrGetKey(?string $key): string
    {
        if (!empty($key)) {
            $this->updateConfigWebhookKey($key);

            return $key;
        }

        $key = $this->getConfigWebhookKey();

        if ($key !== null) {
            return $key;
        }

        $key = $this->generateWebhookKey();
        $this->updateConfigWebhookKey($key);

        return $key;
    }

    /**
     * Generate a new webhook key
     *
     * @return string
     */
    protected function generateWebhookKey(): string
    {
        return $this->keyGenerator->generate();
    }

    /**
     * Update webhook configuration key
     *
     * @param string $key
     */
    protected function updateConfigWebhookKey(string $key): void
    {
        $this->configService->update(ConfigConstant::WEBHOOK_KEY, $key);
    }

    /**
     * Get configuration webhook key
     *
     * @return string|null
     */
    protected function getConfigWebhookKey(): ?string
    {
        return $this->configService->get(ConfigConstant::WEBHOOK_KEY);
    }

    /**
     * Get webhook route for App URL and key
     *
     * @param string $appUrl
     * @param string $key
     * @return string
     */
    protected function getWebhookUrl(string $appUrl, string $key): string
    {
        $route = route(ConfigConstant::WEBHOOK_ROUTE_NAME, [
            'key' => $key,
        ], false);

        return sprintf(
            '%s/%s',
            rtrim($appUrl, '/'),
            ltrim($route, '/')
        );
    }
}
