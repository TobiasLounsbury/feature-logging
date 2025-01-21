<?php

namespace FeatureLogging\Storage;

use FeatureLogging\Contracts\Storage;

class ClosureStorageDriver implements Storage
{
    public function __construct(private readonly \Closure $get, private readonly \Closure $set)
    {
    }

    public function fetchFeatureLevels(): array
    {
        return ($this->get)();
    }

    public function persistFeatureLevels(array $featureLevels): void
    {
        ($this->set)($featureLevels);
    }
}
