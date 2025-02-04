<div>
    @php
        //use FeatureLogging\Facades\FeatureLogging;
        use Illuminate\Support\Facades\Config;

        $featureLevels = [];//FeatureLogging\Facades\FeatureLogging::getFeatureLevels();
        $storageMethod = Config::get('feature_logging.storage_method', 'cache');
    @endphp
    <p class="text-3xl">Feature Logging</p>
    <table>
        <thead>
        <tr>
            <th>Feature</th>
            <th>Level</th>
            @if($storageMethod === 'cache')
                <th>ttl</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($featureLevels as $featureName => $level)
            <tr>
                <td>{{$featureName}}</td>
                <td>{{$level}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
