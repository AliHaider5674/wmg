<?php

namespace App\Preorder\Providers;

use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasMigrations;

/**
 * Class ShopifyServiceProvider
 * @package App\Preorder
 */
class PreorderServiceProvider extends ModuleServiceProvider
{
    use HasMigrations;
    /**
     * Register service providers
     */
    public function register(): void
    {
    }
}
