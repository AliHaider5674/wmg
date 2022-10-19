<?php
namespace App\DataMapper\Providers;

use App\DataMapper\DataExtractor;
use App\DataMapper\Extractors\CallableExtractor;
use App\DataMapper\Extractors\LinkExtractor;
use App\DataMapper\Extractors\StaticExtractor;
use WMGCore\Providers\ModuleServiceProvider;

/**
 * @class DataMapperProvider
 */
class DataMapperProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->app->when(DataExtractor::class)
            ->needs('$extractors')
            ->give(function ($app) {
                return [
                    'link' => $app->make(LinkExtractor::class),
                    'callable' => $app->make(CallableExtractor::class),
                    'static' => $app->make(StaticExtractor::class)
                ];
            });
    }
}
