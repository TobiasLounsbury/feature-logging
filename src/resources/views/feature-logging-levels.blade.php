<form @if($mode === 'livewire') wire:submit="saveFeatureLevels" @endif id="{{$uuid}}:featureLoggingForm">
    @csrf
    <div id="{{$uuid}}:featureLoggingFeatureList" class="feature-logging-feature-list">
    @foreach($featureLevels as $featureName => $level)
        <div class="feature-logging-feature-list-item">
            <span class="feature-logging-feature-name">{{$featureName}}: </span>

            <select class="feature-logging-feature-level" name="featureLevels[{{$featureName}}]{{ $levelName }}">
                @foreach($levels as $levelValue => $levelName)
                    <option value="{{$levelValue}}" @if($level === $levelValue) selected @endif>{{$levelName}}</option>
                @endforeach
            </select>

            @if($storageMethod === 'cache')
                <span>ttl: </span>
                <input type="number" name="featureLevels[{{$featureName}}][ttl]" size="3" />
            @endif
            @if($allowRemoveFeatures)
                <button type="button" class="feature-logging-remove-feature-button">X</button>
            @endif
        </div>
    @endforeach
    </div>

    <hr />
    @if($allowNewFeatures)
        <div style="display:none;" id="{{$uuid}}:newFeatureTemplate"  class="feature-logging-feature-list-item">
            <span class="feature-logging-feature-name">{{$featureName}}: </span>

            <select class="feature-logging-feature-level">
                @foreach($levels as $levelValue => $levelName)
                    <option value="{{$levelValue}}">{{$levelName}}</option>
                @endforeach
            </select>

            @if($storageMethod === 'cache')
                <span>ttl: </span>
                <input type="number" class="feature-logging-feature-ttl" size="3" />
            @endif
            @if($allowRemoveFeatures)
                <button type="button" class="feature-logging-remove-feature-button">X</button>
            @endif
        </div>
        <div>
            <input id="{{$uuid}}:newFeatureName" type="text" />
            <button id="{{$uuid}}:newFeatureButton" type="button">Add new feature</button>
        </div>
        <hr />
    @endif
    <button type="submit" :class="$buttonClass">Save</button>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const newFeatureNameInput = document.getElementById("{{$uuid}}:newFeatureName");
            const newFeatureTemplate = document.getElementById("{{$uuid}}:newFeatureTemplate");
            const featureList = document.getElementById("{{$uuid}}:featureLoggingFeatureList");
            const newFeatureButton = document.getElementById("{{$uuid}}:newFeatureButton");

            if(newFeatureButton) {
                newFeatureButton.addEventListener("click", function() {

                    let newFeature = newFeatureTemplate.cloneNode(true);
                    newFeature.removeAttribute('id');

                    let newFeatureName = newFeatureNameInput.value.replace(' ', '_');

                    newFeature.querySelector(".feature-logging-feature-name").innerText = newFeatureName;

                    let ttl = newFeature.querySelector("input.feature-logging-feature-ttl");
                    if (ttl) {
                        ttl.setAttribute('name', "featureLevels[" + newFeatureName + "][ttl]");
                    }

                    newFeature.querySelector(".feature-logging-feature-level").setAttribute('name', "featureLevels[" + newFeatureName + "]{{ $levelName }}");

                    newFeatureNameInput.value = "";
                    featureList.append(newFeature);
                    newFeature.style.display = 'block';
                });
            }

            document.querySelectorAll("#{{$uuid}}featureLoggingForm .feature-logging-remove-feature").forEach((btn) => {
                btn.addEventListener("click", function(event) {
                    event.target.closest(".feature-logging-feature-list-item").remove();
                });
            });
        });
    </script>
</form>
