<div class="card mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-lg-6">
                <div class="form-input">
                    <label for="models" class="form-label">{{ localize('Model') }} <x-required-star /> </label>
                    <select class="form-select select2" id="models" name="model" required>
                   
                        @isset($ttsModels)                            
                            @foreach ($ttsModels as $model)
                                <option value='{{ $model->model_id }}'>{{ $model->name }}</option>
                            @endforeach
                        @endisset

                    </select>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="form-input">
                    <label for="voice" class="form-label">{{ localize('Voice') }} <x-required-star /></label>
                    <select class="form-select select2 voiceSelect" id="voiceSelect" name="voice" required>
                        @isset($languages_voices)                            
                            @foreach ($languages_voices as $voice)
                                <option value='{{ $voice->voice_id }}'>{{ $voice->name }} [{{$voice->accent}} ] [{{$voice->description}} ] [{{$voice->use_case}} ]</option>
                            @endforeach
                        @endisset

                    </select>
                </div>
            </div>
        
            <div class="col-lg-12">
                <div class="form-input">
                    <label for="stability" class="form-label d-block">{{ localize('Stability') }}</label>
                    <input class="range-slider__range"  name="stability" id="stability" type="range" value="{{isset($defaultVoiceSetting) && $defaultVoiceSetting->stability * 100}}" value="0"
                        min="0" max="100">
                    <span id="stability__value" class="range-slider__value">{{isset($defaultVoiceSetting) && $defaultVoiceSetting->stability * 100 }}</span>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="form-input">
                    <label for="similarity_boost"
                        class="form-label d-block">{{ localize('Clarity + Similarity Enhancement') }}
                    </label>
                    <input class="range-slider__range" id="similarity_boost" name="similarity_boost" type="range"
                        value="{{ isset($defaultVoiceSetting) && $defaultVoiceSetting->similarity_boost * 100}}" min="0" max="100">

                    <span id="similarity_boost__value"
                        class="range-slider__value">{{isset($defaultVoiceSetting) && $defaultVoiceSetting->similarity_boost * 100 }}</span>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-input">
                    <label for="style" class="form-label d-block">{{ localize('Style Exaggeration') }}
                    </label>
                    <input class="range-slider__range" name="style" id="style" type="range"
                        value="{{isset($defaultVoiceSetting) && $defaultVoiceSetting->style * 100 }}" min="0" max="100">

                    <span id="style__value" class="range-slider__value">{{isset($defaultVoiceSetting) && $defaultVoiceSetting->style * 100 }}</span>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" name="use_speaker_boost"
                        {{ isset($defaultVoiceSetting) && $defaultVoiceSetting->use_speaker_boost == true ? 'checked' : '' }} type="checkbox"
                        id="use_speaker_boost" checked>
                    <label class="form-check-label" for="use_speaker_boost">{{ localize('Speaker Boost') }}</label>
                </div>
            </div>
        </div>
    </div>
</div>
