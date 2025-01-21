<?php

namespace FeatureLogging;

use FeatureLogging\Traits\usesFeatureLoggingLevels;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;

class FeatureLoggingManager extends LogManager
{

    use usesFeatureLoggingLevels;

    protected $wrappedChannels = [];


    public function build(array $config)
    {
        unset($this->wrappedChannels['ondemand']);
        return parent::build($config);
    }


    public function feature(string $featureName): LoggerInterface
    {
        return $this->driver($this->getFeatureChannelName($featureName));
    }


    public function forgetFeature(string $featureName): void
    {
        $this->forgetChannel($this->getFeatureChannelName($featureName));
    }

    public function forgetChannel($driver = null): void
    {
        unset($this->wrappedChannels[$driver]);
        parent::forgetChannel($driver);
    }


    protected function configurationFor($name)
    {
        //Check if the driver requested is a feature and if so return
        //whatever channel config is set as the feature channel
        if ($this->isFeatureChannel($name)) {
            return $this->getFeatureConfig($this->removeFeaturePrefix($name));
        }

        return $this->app['config']["logging.channels.{$name}"];
    }


    protected function featureEnabled($featureName): bool
    {
        return $this->getFeatureLevel($featureName) !== 'disabled';
    }


    protected function get($name, ?array $config = null)
    {
        if($this->app['config']["feature_logging.include_helpers"]) {
            return $this->wrappedChannels[$name] ??
                with(new FeatureLoggingWrapper(parent::get($name, $config)), function ($wrapped) use ($name) {
                    return $this->wrappedChannels[$name] = $wrapped;
                });
        }
        return parent::get($name, $config);
    }


    protected function getFeatureChannelName(string $featureName): string
    {
        return $this->getFeaturePrefix().$featureName;
    }


    protected function getFeatureConfig(string $featureName)
    {
        $config = $this->getFeatureLoggingChannelConfig();

        //Replace the %feature% placeholder in the config where it exists
        array_walk_recursive($config, function (&$value) use ($featureName) {
            if (is_string($value)) {
                $value = str_replace('%feature%', $featureName, $value);
            }
        });

        //Set the level
        $config['level'] = $this->getFeatureLevel($featureName);

        return $config;
    }


    protected function getFeatureLoggingChannelConfig()
    {
        $featureChannel = $this->app['config']["feature_logging.log_channel"] ?? 'feature';
         return $this->app['config']["logging.channels.{$featureChannel}"] ??
             $this->app['config']["logging.channels.{$this->getDefaultDriver()}"];
    }

    protected function getFeaturePrefix(): string
    {
        return ($this->app['config']["feature_logging.prefix"] ?? 'feature').':';
    }


    protected function getNullDriver(): string
    {
        return $this->app['config']["feature_logging.null_driver"] ?? 'null';
    }


    protected function isFeatureChannel(string $name): bool
    {
        return str_starts_with($name, $this->getFeaturePrefix());
    }


    protected function parseDriver($driver)
    {
        $driver = parent::parseDriver($driver);

        //Check if the driver/channel requested is a feature
        // and if that feature is disabled, return the null driver
        if ($this->isFeatureChannel($driver)) {
            return $this->featureEnabled($this->removeFeaturePrefix($driver)) ? $driver : $this->getNullDriver();
        }

        return $driver;
    }


    protected function removeFeaturePrefix($driver)
    {
        return substr($driver, strlen($this->getFeaturePrefix()));
    }
}
