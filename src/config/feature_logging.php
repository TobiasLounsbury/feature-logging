<?php

/**
 * Feature Logging configuration file.
 */

use FeatureLogging\FeatureLoggingManager;

return [


    /*
    |--------------------------------------------------------------------------
    | Storage Method
    |--------------------------------------------------------------------------
    | (string)
    |
    | This option defines the way that log levels for the various features are
    | defined/persisted.
    |
    |
    | config:
    |   Loads the features and their levels from the config, this option
    |   makes feature levels immutable and disables the UI for adjusting
    |   feature levels on the fly.
    |
    | cache:
    |   This method stores the features and logging level in the
    |   cache, using the Cache facade, as configured in cache_storage
    |
    | custom:
    |   This method allows a custom storage solution that can be
    |   invoked through the custom_storage config.
    |
    */
    "storage_method" => env('FEATURE_LOGGING_STORAGE_METHOD', 'cache'),


    /*
    |--------------------------------------------------------------------------
    | Cache Storage Configuration
    |--------------------------------------------------------------------------
    | (array)
    |
    | Setting up how a cache storage solution should work.
    |
    | prefix: (string), default: 'FeatureLogging'
    |   The prefix to use for any cache keys persisted. Used to avoid cache key clash
    |
    | store: (?string), default: null
    |   The name of the cache store to use for caching feature configs. Default to
    |   null, or the default cache store.
    |
    | individually: (bool), default: false
    |   True - will cause each feature to be cached with it's own key in the cache
    |   False - will cause all feature levels to be cached together in a key
    |
    | ttl: (?int), default: null
    |   How long to persist cache keys. This is specifically useful when you want
    |   to change the log level for features for a short time and have it/them
    |   automatically revert.
    |
    */
    "cache_storage" => [
        "prefix" => env('FEATURE_LOGGING_CACHE_PREFIX', 'FeatureLogging'),
        "store" => env('FEATURE_LOGGING_CACHE_STORE', null),
        "individually" => env('FEATURE_LOGGING_CACHE_INDIVIDUALLY', false),
        "ttl" => env('FEATURE_LOGGING_CACHE_TTL', null),
    ],



    /*
    |--------------------------------------------------------------------------
    | Custom Storage
    |--------------------------------------------------------------------------
    | (array|string|callable)
    |
    | Defines how custom storage is to be triggerd.
    |
    | May be Defined in on of three ways:
    |
    | 1) As a classname string for a class that implements
    |    FeatureLogging\Contracts\Storage
    |
    | 2) As a callable that when invoked returns an object that implements
    |    FeatureLogging\Contracts\Storage
    |
    | 3) As an array with two keys "get" and "set" each a callable
    |   get:
    |       Should return an array of key value pairs where the keys are the
    |       names of the features and the values are the log levels as defined
    |       by monolog/levels, or a -1 or the word disabled for a feature with
    |       no logging enabled.
    |
    |   set:
    |       Accepts an array, and persists it's contents
    |
    */
    #"custom_storage" => [],


    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    | (string)
    |
    | The name of the logging channel to use when creating feature channels.
    | The channel itself will be defined in config/logging, the same way any
    | channel is defined, and may use any driver(s).
    |
    | The 'path' key may use a replacement value %feature% that will be replaced
    | with the name of the feature when it is instantiated.
    |
    */
    "log_channel" => env('FEATURE_LOGGING_CHANNEL', 'feature'),

    /*
    |--------------------------------------------------------------------------
    | Null Channel
    |--------------------------------------------------------------------------
    | (string)
    |
    | The logging channel to use when no logging is to be done. The laravel
    | default is to use 'null' but you may define a custom channel if you wish.
    |
    */
    "null_driver" => env('FEATURE_LOGGING_NULL_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Feature Log Channel Prefix
    |--------------------------------------------------------------------------
    | (string)
    |
    | Prefix that will be used when creating a feature log channel.
    | Features can be logged to either via the facade::feature() function
    | Log::feature('featureName')
    | or by requesting a log channel directly using this prefix
    | logger()->channel('{prefix}:featureName')
    |
    */
    "prefix" => env('FEATURE_LOGGING_PREFIX', 'feature'),



    /*
    |--------------------------------------------------------------------------
    | Default Log Level
    |--------------------------------------------------------------------------
    | (string)
    |
    | The default level to apply to any feature that is requested but doesn't
    | have a log level specifically defined in storage.
    | And of the Monolog logging levels are acceptable as well as the two
    | keywords:
    |
    | disabled - No logging for a feature that hasn't been defined
    |
    | system -   Defaults to whatever the log level is in the chanel defined
    |            by log_channel
    |
    */
    "default_level" => env('FEATURE_LOGGING_DEFAULT_LEVEL', 'disabled'),


    /*
    |--------------------------------------------------------------------------
    | Fallback on Config
    |--------------------------------------------------------------------------
    | (bool) Default: True
    |
    | If a feature is not defined in the configured storage method, should we
    | we look in the config for the log level for that feature.
    |
    | This is particularly useful if you want to define most features at a
    | warning level or above in the config, and then use individual cache storage
    | with custom cache TTLs to temporarily turn up/down logging for a specific
    | feature, and have it auto-revert to it's default level without human
    | intervention.
    |
    */
    "fallback_on_config" => env('FEATURE_LOGGING_CONFIG_FALLBACK', true),


    /*
    |--------------------------------------------------------------------------
    | Include Log Helpers
    |--------------------------------------------------------------------------
    | (bool) Default: True
    |
    | If a feature is not defined in the configured storage method, should we
    | we look in the config for the log level for that feature.
    |
    */
    "include_helpers" => env('FEATURE_LOGGING_INCLUDE_HELPERS', true),

    /*
    |--------------------------------------------------------------------------
    | Feature Names and Log Levels
    |--------------------------------------------------------------------------
    | (array)
    |
    | A key/value map of feature names and corresponding log levels
    |
    | Allowed log levels:
    | 'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info',
    | 'debug', 'disabled'
    |
    */
    "features" => array_reduce(explode(',', env('FEATURE_LOGGING_FEATURES', "")),
        function($features, $featureString) {
            if ($featureString) {
                [$featureName, $level] = explode(':', $featureString);
                $features[$featureName] = $level;
            }
            return $features;
        }, [
            //"featureName" => "LogLevel",
            //example:
            //"reports" => "warning",
            //"export" => "disabled",
        ]),
];
