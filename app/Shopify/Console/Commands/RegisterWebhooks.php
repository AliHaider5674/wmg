<?php

namespace App\Shopify\Console\Commands;

use Exception;
use Illuminate\Console\Command;

/**
 * Class RegisterWebhooks
 * @package App\Shopify\Console\Commands
 */
class RegisterWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<SIGNATURE
shopify:webhooks:register
{--k|key= : Key that must be passed as a part of the URL, otherwise use configuration}
{--u|app-url= : Application URL in case it should be different than the environment}
{--g|generate : Force generating a new key even if a key is already set (Should not be used with -k|--key)}
SIGNATURE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register Webhooks with Shopify';

    /**
     *
     */
    public function handle(): void
    {
        $this->line('Add logic here');
    }
}
