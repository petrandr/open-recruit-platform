<div class="p-4 py-4">
    <!-- Template for a new screening question -->
    <template id="screening-question-template">
        <div class="screening-item mb-3 row" data-index="__INDEX__">
            <div class="col-md-5">
                <label>{{ __('Question') }}</label>
                <input type="text"
                       name="screeningQuestions[__INDEX__][question_text]"
                       class="form-control"
                       list="question-list"
                       value=""
                       required="required">
            </div>
            <div class="col-md-3">
                <label>{{ __('Type') }}</label>
                <select name="screeningQuestions[__INDEX__][question_type]"
                        class="form-select question-type" required="required">
                    <option value="number" selected>{{ __('Number') }}</option>
                    <option value="yes/no">{{ __('Yes / No') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>{{ __('Value') }}</label>
                <input type="number"
                       name="screeningQuestions[__INDEX__][min_value]"
                       class="form-control min-field number-field"
                       value=""
                       required="required">
                <select name="screeningQuestions[__INDEX__][min_value]"
                        class="form-select min-field boolean-field"
                        disabled style="display:none;" required="required">
                    <option value="">{{ __('Select Yes/No') }}</option>
                    <option value="1">{{ __('Yes') }}</option>
                    <option value="0">{{ __('No') }}</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-screening-question">
                    {{ __('Remove') }}
                </button>
            </div>
        </div>
    </template>
    <div id="screening-questions-container">
        @foreach(old('screeningQuestions', $job->screeningQuestions->toArray()) as $i => $q)
            <div class="screening-item mb-3 row" data-index="{{ $i }}">
                {{-- Preserve existing question ID for update --}}
                @if(!empty($q['id']))
                    <input type="hidden" name="screeningQuestions[{{ $i }}][id]" value="{{ $q['id'] }}" required="required"/>
                @endif
                <div class="col-md-5">
                    <label>{{ __('Question') }}</label>
                    <input type="text"
                           name="screeningQuestions[{{ $i }}][question_text]"
                           class="form-control"
                           list="question-list"
                           value="{{ old('screeningQuestions.'.$i.'.question_text', $q['question_text'] ?? '') }}"
                           required="required">
                </div>
                <div class="col-md-3">
                    <label>{{ __('Type') }}</label>
                    <select name="screeningQuestions[{{ $i }}][question_type]"
                            class="form-select question-type" required="required">
                        <option value="number" @selected(old('screeningQuestions.'.$i.'.question_type', $q['question_type'] ?? '') === 'number')>{{ __('Number') }}</option>
                        <option value="yes/no" @selected(old('screeningQuestions.'.$i.'.question_type', $q['question_type'] ?? '') === 'yes/no')>{{ __('Yes / No') }}</option>
                    </select>
                </div>
                @php
                    $type = old('screeningQuestions.'.$i.'.question_type', $q['question_type'] ?? 'number');
                    $minValue = old('screeningQuestions.'.$i.'.min_value', $q['min_value'] ?? '');
                @endphp
                <div class="col-md-2">
                    <label>{{ __('Value') }}</label>
                    <input type="number"
                           name="screeningQuestions[{{ $i }}][min_value]"
                           class="form-control min-field number-field"
                           value="{{ $minValue }}"
                           required="required"
                           @if($type !== 'number') disabled style="display:none;" @endif>
                    <select name="screeningQuestions[{{ $i }}][min_value]"
                            class="form-select min-field boolean-field"
                            required="required"
                            @if($type !== 'yes/no') disabled style="display:none;" @endif>
                        <option value="">{{ __('Select Yes/No') }}</option>
                        <option value="yes" @selected($minValue === 'yes')>{{ __('Yes') }}</option>
                        <option value="no" @selected($minValue === 'no')>{{ __('No') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-screening-question">
                        {{ __('Remove') }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-secondary mb-3" id="add-screening-question">
        {{ __('Add Question') }}
    </button>
    <!-- Dynamic screening script moved to resources/js/custom.js -->
</div>
