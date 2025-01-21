<?php

namespace FeatureLogging\Storage;

use FeatureLogging\Contracts\Storage;
use FeatureLogging\Traits\parsesFeatureLoggingLevels;
use Illuminate\Support\Facades\Config;

class ConfigStorageDriver implements Storage
{
    use parsesFeatureLoggingLevels;

    public function fetchFeatureLevels(): array
    {
        $featureLevels = Config::get('feature_logging.features', []);

        return (is_string($featureLevels) )?
            $this->parseFeatureLevelsFromString($featureLevels) :
            $featureLevels;
    }

    public function persistFeatureLevels(array $featureLevels): void
    {
        //Set it in the container since that is the best we can do at persisting
        Config::set('feature_logging.features', $featureLevels);
    }
}
