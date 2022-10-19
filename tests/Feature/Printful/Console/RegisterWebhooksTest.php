<?php declare(strict_types=1);

namespace Tests\Feature\Printful\Console;

use App\Printful\Service\WebhookKeyGenerator;
use Illuminate\Support\Str;
use Printful\PrintfulApiClient;
use Tests\TestCase;
use Mockery as M;
use WMGCore\Configuration as Config;

/**
 * Class RegisterWebhooksTest
 * @package Tests\Feature\Printful\Console
 */
class RegisterWebhooksTest extends TestCase
{
    /**
     * Webhooks To Enable
     */
    private const WEBHOOKS_TO_ENABLE = [
        'package_shipped',
        'package_returned',
        'order_put_hold',
    ];

    /**
     * App URL constant
     */
    private const APP_URL_CONSTANT = 'app.url';

    /**
     * Webhook key config path
     */
    private const WEBHOOK_KEY_CONFIG = 'printful.webhook.key';

    /**
     * @var PrintfulApiClient
     */
    private $printfulApi;

    /**
     * @var WebhookKeyGenerator
     */
    private $webhookKeyGenerator;

    /**
     * Setup tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->printfulApi = M::mock(PrintfulApiClient::class);
        $this->app->singleton(PrintfulApiClient::class, function () {
            return $this->printfulApi;
        });

        $this->webhookKeyGenerator = M::mock(WebhookKeyGenerator::class);
        $this->app->singleton(WebhookKeyGenerator::class, function () {
            return $this->webhookKeyGenerator;
        });
    }

    /**
     * Test register webhook
     */
    public function testRegisterWebhooksCommandMakesApiRequestWithConfigAppUrlAndNewlyGeneratedKey(): void
    {
        $key = Str::random(32);
        $this->generatesKey($key);
        $appUrl = 'https://' . $this->faker->domainName;
        config([self::APP_URL_CONSTANT => $appUrl]);

        $this->printfulApiShouldReceiveWebhookRegisterRequest(
            $this->buildUrl(
                $appUrl,
                $key
            ),
            self::WEBHOOKS_TO_ENABLE
        );

        $this->artisan('printful:webhooks:register');
    }

    /**
     * Test that supplying a explicit key to the register webhooks command will
     * change the key to that key
     */
    public function testRegisterWebhooksCommandWithKeyOptionChangesWebhookKey(): void
    {
        $initialValue = 'abcdefghijklmnopqrstuvwxyz';

        $config = Config::where('path', self::WEBHOOK_KEY_CONFIG)->first()
            ?? Config::create([
                'path' => self::WEBHOOK_KEY_CONFIG,
                'value' => $initialValue,
                'is_secured' => 0,
            ]);

        self::assertEquals($initialValue, $config->value);

        $this->assertDatabaseHas('configurations', [
            'path' => self::WEBHOOK_KEY_CONFIG,
            'value' => $config->value,
        ]);


        $newKey = Str::random(32);

        $appUrl = 'https://' . $this->faker->domainName;
        config([self::APP_URL_CONSTANT => $appUrl]);

        $this->printfulApiShouldReceiveWebhookRegisterRequest(
            $this->buildUrl(
                $appUrl,
                $newKey
            ),
            self::WEBHOOKS_TO_ENABLE
        );

        $this->artisan('printful:webhooks:register', [
            '--key' => $newKey
        ]);


        $this->assertDatabaseHas('configurations', [
            'path' => self::WEBHOOK_KEY_CONFIG,
            'value' => $newKey,
        ]);
    }

    /**
     * Printful API should receive a webhook registration request with a URL and
     * types
     *
     * @param string $url
     * @param array  $types
     */
    private function printfulApiShouldReceiveWebhookRegisterRequest(
        string $url,
        array $types
    ): void {
        $this->printfulApi->expects('post')->withArgs(
            static function (string $path, array $data) use ($url, $types) {
                self::assertEquals('webhooks', $path);

                self::assertEquals($url, $data['url']);
                self::assertCount(count($types), $data['types']);

                foreach ($types as $type) {
                    self::assertContains($type, $data['types']);
                }

                return true;
            }
        )->andReturn([
            "code" => 200,
            "result" => [
                "url" => $url,
                "types" => $types,
                "params" => [],
            ],
        ]);
    }

    /**
     * Build URL with app URL an key
     *
     * @param string $appUrl
     * @param string $key
     * @return string
     */
    private function buildUrl(string $appUrl, string $key): string
    {
        return sprintf(
            '%s/api/printful/webhook/%s',
            rtrim($appUrl, '/'),
            ltrim($key, '/')
        );
    }

    /**
     * Specify specific key to generate with mocked key generator
     *
     * @param string $key
     * @return void
     */
    private function generatesKey(string $key): void
    {
        $this->webhookKeyGenerator
            ->expects('generate')
            ->withNoArgs()
            ->andReturn($key);
    }
}
