<?php

namespace FeatureLogging\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use FeatureLogging\Views\Components\FeatureLoggingLevels;

class FeatureLoggingServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'FeatureLogging');
        Blade::component('feature-logging-levels', FeatureLoggingLevels::class);

        $this->publishes([
            __DIR__ . '/../config/feature_logging.php' => config_path('feature_logging.php'),
        ]);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/feature_logging.php', 'feature_logging'
        );
    }

}
