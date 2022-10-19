<?php

namespace App\ArgumentValidator\Providers;

use App\ArgumentValidator\ArgumentValidator;
use Illuminate\Support\ServiceProvider;

/**
 * ArgumentValidator provider
 *
 * Class ArgumentValidatorServiceProvider
 * @category WMG
 * @package  App\ArgumentValidator
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ArgumentValidatorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('argumentValidator', ArgumentValidator::class);
    }
}
