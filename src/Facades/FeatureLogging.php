<?php

namespace FeatureLogging\Facades;

use FeatureLogging\FeatureLoggingManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Psr\Log\LoggerInterface;

/**
 * @method static LoggerInterface feature(string $featureName)
 * @method static void flushLastMessages(string $channel = null)
 * @method static void forgetFeature(string $featureName)
 * @method static string getDefaultFeatureLevel()
 * @method static array getFeatureLevels()
 * @method static string getFeatureLevel(string $featureName)
 * @method static void setDefaultFeatureLevel(string $level)
 * @method static void setFeatureLevel(string $featureName, string|int $level)
 * @method static void setFeatureLevels(array $featureLevels)
 * @method static array fetchFeatureLevels()
 * @method static void persistFeatureLevels(array $featureLevels)
 *
 * @see \FeatureLogging\FeatureLoggingManager
 *
 * @mixin \Illuminate\Log\LogManager
 *
 */
class FeatureLogging extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FeatureLoggingManager::class;
    }
}
