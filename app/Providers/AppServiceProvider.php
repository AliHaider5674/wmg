<?php

namespace App\Providers;

use App\Models\Schedule;
use App\Core\Services\ExternalService;
use App\Services\AlertEventService;
use WMGCore\Services\ConfigService;
use App\Services\FileSystemService;
use App\Services\ServiceEventCallService;
use App\Services\ThreadService;
use Illuminate\Support\ServiceProvider;

/**
 * Core app provider
 *
 * Class AppServiceProvider
 * @category WMG
 * @package  App\Providers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ThreadService::class);
        $this->app->singleton(ServiceEventCallService::class);
        $this->app->singleton(FileSystemService::class);
        $this->app->singleton(AlertEventService::class);
    }
}
