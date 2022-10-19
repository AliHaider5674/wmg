<?php declare(strict_types=1);

namespace App\Printful\Service;

use Illuminate\Support\Str;

/**
 * Class WebhookKeyGenerator
 * @package App\Printful\Service
 */
class WebhookKeyGenerator
{
    /**
     * Generate key
     */
    public function generate(): string
    {
        return Str::random(64);
    }
}
