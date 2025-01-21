<?php

namespace FeatureLogging\Views\Components;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Component;
use FeatureLogging\Facades\FeatureLogging;

class FeatureLoggingLevels extends Component
{
    public string $uuid;

    public string $storageMethod;

    public array $featureLevels;


    public array $levels = [
        'disabled' => 'Disabled',
        'system' => 'System',
        'debug' => 'Debug',
        'info' => 'Info',
        'notice' => 'Notice',
        'warning' => 'Warning',
        'error' => 'Error',
        'critical' => 'Critical',
        'alert' => 'Alert',
        'emergency' => 'Emergency',
    ];
    public function __construct(
        public ?string $mode = 'view',
        public ?string $buttonClass = 'btn btn-primary',
        public ?string $buttonText = 'Text',
    )
    {
        $this->uuid = uniqid();

        if(!in_array($this->mode, ['view', 'alpine', 'livewire'])) {
            $this->mode = 'view';
        }

        $this->storageMethod = Config::get('feature_logging.storage_method', 'cache');
        $this->levelName = ($this->storageMethod === 'Cache') ? '[level]' : '';
    }


    public function boot()
    {
        $this->featureLevels = FeatureLogging::getFeatureLevels();
    }

    public function render()
    {
        return view('FeatureLogging::feature-logging-levels');
    }

    public function saveFeatureLevels()
    {

    }
}
