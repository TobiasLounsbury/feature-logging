<?php

namespace FeatureLogging\Traits;

use FeatureLogging\Contracts\Storage;
use FeatureLogging\Storage\CacheStorageDriver;
use FeatureLogging\Storage\ConfigStorageDriver;
use FeatureLogging\Storage\ClosureStorageDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Monolog\Level;

trait usesFeatureLoggingLevels
{
    private array $featureLevels = [];

    private array $featureLoggingConfig = [];

    private Storage $featureLoggingStorageDriver;

    private string $defaultFeatureLoggingLevel;


    public function getDefaultFeatureLevel(): string
    {
        if (!isset($this->defaultFeatureLoggingLevel)) {

            $config = Config::get([
                'feature_logging.default_level' => 'disabled',
                'logging.channels' => null,
                'feature_logging.log_channel' => 'feature',
                'logging.default' => null
            ]);


            $this->defaultFeatureLoggingLevel = strtolower($config['feature_logging.default_level']);
            if ($this->defaultFeatureLoggingLevel === 'system') {
                $this->defaultFeatureLoggingLevel = strtolower(Arr::get($config['logging.channels'], "{$config['feature_logging.log_channel']}.level") ??
                    Arr::get($config['logging.channels'], "{$config['logging.default']}.level") ??
                    'disabled');
            }
        }
        return $this->defaultFeatureLoggingLevel;
    }

    public function getFeatureLevel(string $featureName): string
    {
        if ($storedLevel = Arr::get($this->featureLevels ?? $this->loadFeatureLevels(),
            $featureName)) {
            return $this->normalizeLevel($storedLevel);
        }

        $fallback = Config::get('feature_logging.fallback_on_config', true);
        $level = Config::get("feature_logging.features.${featureName}");

        return $this->normalizeLevel($fallback ? $level : null) ?? $this->getDefaultFeatureLevel();
    }

    public function getFeatureLevels(): array
    {
        return $this->featureLevels;
    }

    public function getStorageMethod(): string
    {
        return $this->getFeatureLoggingConfig('storage_method', 'cache');
    }

    public function setDefaultFeatureLevel(string $level): void
    {
        $this->defaultFeatureLoggingLevel = $level;
    }

    public function setFeatureLevel(string $featureName, string|int $level): void
    {
        $this->featureLevels[$featureName] = $level;
    }

    public function setFeatureLevels(array $features): void
    {
        $this->featureLevels = $features;
    }

    public function fetchFeatureLevels(): array
    {
        return $this->getFeatureLoggingStorageDriver()->fetchFeatureLevels();
    }


    public function persistFeatureLevels(array $featureLevels): void
    {
        $featureLevels ??= $this->featureLevels;
        $this->getFeatureLoggingStorageDriver()->persistFeatureLevels($this->featureLevels);
    }


    protected function persistLoadedFeatureLevels(): void
    {
        $this->persistFeatureLevels($this->featureLevels);
    }

    protected function loadFeatureLevels(): array
    {
        return $this->featureLevels = $this->fetchFeatureLevels();
    }

    protected function getFeatureLoggingConfig(string $key = '*', $default = null): mixed
    {
        if(!isset($this->featureLoggingConfig)) {
            $this->featureLoggingConfig = Config::get('feature_logging');
        }

        return Arr::get($this->featureLoggingConfig, $key, $default);
    }

    protected function getFeatureLoggingStorageDriver(): Storage
    {
        return $this->featureLoggingStorageDriver ??= $this->makeFeatureLoggingStorageDriver();
    }

    protected function makeFeatureLoggingStorageDriver($method = null): Storage
    {
        $method ??= $this->getFeatureLoggingConfig('storage_method', 'cache');

        switch ($method) {
            case 'config':
                return new ConfigStorageDriver();

            case 'custom':
                $customStorage = $this->getFeatureLoggingConfig('custom_storage');

                return match(true) {
                    (is_callable($customStorage)) => $customStorage(),
                    (is_array($customStorage)) => new ClosureStorageDriver($customStorage['get'], $customStorage['set']),
                    (is_string($customStorage)) => new $customStorage(),
                    (is_object($customStorage)) => $customStorage,
                };

            case 'cache':
            default:
                return new CacheStorageDriver();
        }
    }

    protected function normalizeLevel(string $level): string
    {
        $level = strtolower($level);

        if (!in_array(strtoupper($level), Level::NAMES) && $level !== 'disabled') {
            return $this->getDefaultFeatureLevel();
        }

        return $level;
    }
}
