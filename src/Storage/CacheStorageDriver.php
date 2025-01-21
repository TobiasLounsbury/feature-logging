<?php

namespace FeatureLogging\Storage;

use FeatureLogging\Contracts\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class CacheStorageDriver implements Storage
{

    private array $config;

    public function __construct($config = null)
    {
        $this->config = $config ?? Config::get('feature-logging.cache_storage', []);
    }

    public function fetchFeatureLevels(): array
    {
        $store = Cache::store($this->config['store']);
        $levels = $store->get($this->makeFeatureKey('features'));

        if ($this->config['individually'] ?? false) {
            $individualLevels = [];
            foreach ($levels as $featureName) {
                $level = $store->get($this->makeFeatureKey('features', $featureName));
                if ($level) {
                    $individualLevels[$featureName] = $level;
                }
            }
            return $individualLevels;
        }

        return $levels;
    }

    public function persistFeatureLevels(array $featureLevels)
    {
        $store = Cache::store($this->config['store']);

        if ($this->config['individually'] ?? false) {
            foreach($featureLevels as $featureName => $level) {
                $store->put($this->makeFeatureKey('features', $featureName),
                    Arr::get($level, 'level', $level),
                    Arr::get($level, 'ttl', $this->config['ttl']));
            }
            $oldKeys = $store->get($this->makeFeatureKey('features'));
            $newKeys = array_keys($featureLevels);
            $store->put($this->makeFeatureKey('features'), $newKeys);
            foreach(array_diff($oldKeys, $newKeys) as $key) {
                $store->forget($this->makeFeatureKey('features', $key));
            }
        } else {
            $store->put($this->makeFeatureKey('features'), $featureLevels, $this->config['ttl']);
        }
    }

    protected function makeFeatureKey(...$keys): string {
        return implode(':', [Arr::get($this->config, 'prefix') ?? 'FeatureLogging', ...$keys]);
    }
}
