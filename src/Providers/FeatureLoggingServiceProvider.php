<?php

namespace FeatureLogging\Providers;

use FeatureLogging\FeatureLoggingManager;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

use FeatureLogging\Facades\FeatureLogging;
use Flashy\McBoatface;

class FeatureLoggingServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'feature-logging');
        //Blade::component('feature-logging-levels', FeatureLoggingLevels::class);
        //Blade::componentNamespace('FeatureLogging\\Views\\Components', 'feature-logging');


//        if(Config::get('feature_logging.include_helpers')) {
//            Queue::failing(function (JobFailed $event) {
//                FeatureLogging::flushLastMessages();
//            });
//
//            Queue::after(function (JobProcessed $event) {
//                FeatureLogging::flushLastMessages();
//            });
//        }

        $this->publishes([
            __DIR__ . '/../config/feature_logging.php' => config_path('feature_logging.php'),
        ]);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('feature-log', fn ($app) => new FeatureLoggingManager($app));

        $this->mergeConfigFrom(
            __DIR__.'/../config/feature_logging.php', 'feature_logging'
        );
    }

}
