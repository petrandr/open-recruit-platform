<div class="p-4 py-4">
  <div id="screening-questions-container">
    @foreach(old('screeningQuestions', $job->screeningQuestions->toArray()) as $i => $q)
      <div class="screening-item mb-3 row" data-index="{{ $i }}">
        {{-- Preserve existing question ID for update --}}
        @if(!empty($q['id']))
          <input type="hidden" name="screeningQuestions[{{ $i }}][id]" value="{{ $q['id'] }}" />
        @endif
        <div class="col-md-5">
          <label>{{ __('Question') }}</label>
          <input type="text"
                 name="screeningQuestions[{{ $i }}][question_text]"
                 class="form-control"
                 list="question-list"
                 value="{{ old('screeningQuestions.'.$i.'.question_text', $q['question_text'] ?? '') }}">
        </div>
        <div class="col-md-3">
          <label>{{ __('Type') }}</label>
          <select name="screeningQuestions[{{ $i }}][question_type]"
                  class="form-select question-type">
            <option value="number" @selected(old('screeningQuestions.'.$i.'.question_type', $q['question_type'] ?? '') === 'number')>{{ __('Number') }}</option>
            <option value="boolean" @selected(old('screeningQuestions.'.$i.'.question_type', $q['question_type'] ?? '') === 'boolean')>{{ __('Yes / No') }}</option>
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
                 @if($type !== 'number') disabled style="display:none;" @endif>
          <select name="screeningQuestions[{{ $i }}][min_value]"
                  class="form-select min-field boolean-field"
                  @if($type !== 'boolean') disabled style="display:none;" @endif>
            <option value="">{{ __('Select Yes/No') }}</option>
            <option value="1" @selected($minValue === '1')>{{ __('Yes') }}</option>
            <option value="0" @selected($minValue === '0')>{{ __('No') }}</option>
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
