<?php

namespace FeatureLogging\Contracts;

interface Storage
{
    public function fetchFeatureLevels(): array;

    public function persistFeatureLevels(array $featureLevels);
}