<?php

namespace App\Printful\Console\Commands;

use App\Printful\Configurations\PrintfulConfig;
use Exception;
use App\Printful\Service\WebhookApiService;
use Illuminate\Console\Command;

/**
 * Class RegisterWebhooks
 * @package App\Printful\Console\Commands
 */
class RegisterWebhooks extends Command
{
    private const KEY_AND_GENERATE_OPTIONS = <<<MSG
You cannot pass in both --key and --generate. Generate will generate a new key for you. If you already have a key
that you want to use, you can just pass it in using --key.
MSG;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<SIGNATURE
printful:webhooks:register 
{--k|key= : Key that must be passed as a part of the URL, otherwise use configuration}
{--u|app-url= : Application URL in case it should be different than the environment}
{--g|generate : Force generating a new key even if a key is already set (Should not be used with -k|--key)}
SIGNATURE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register Webhook URL with Printful';

    /**
     * Execute the console command.
     *
     * @param WebhookApiService $webhookApiService
     * @param PrintfulConfig  $printfulConfig
     */
    public function handle(
        WebhookApiService $webhookApiService,
        PrintfulConfig $printfulConfig
    ): void {
        $key = $this->option('key');
        $appUrl = $this->option('app-url');
        $generate = $this->option('generate');

        if ($generate && $key) {
            $this->multiLineError(self::KEY_AND_GENERATE_OPTIONS);
            return;
        }

        $webhooks = $printfulConfig->getEnabledWebhooks();

        try {
            $webhookApiService->registerWebhooks($webhooks, $key, $appUrl);
        } catch (Exception $e) {
            $this->multiLineError($e->getMessage());
            return;
        }

        $this->line($key);
    }

    /**
     * Echo multi line error one line at a time to fix formatting issues
     *
     * @param string $message
     */
    protected function multiLineError(string $message): void
    {
        foreach (explode(PHP_EOL, $message) as $line) {
            $this->error($line);
        }
    }
}
