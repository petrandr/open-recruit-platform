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
  <script>
  (function() {
    // Initialize event handlers, override to prevent duplicates
    function initScreening() {
      const container = document.getElementById('screening-questions-container');
      if (!container) return;

      const addBtn = document.getElementById('add-screening-question');
      if (addBtn) {
        addBtn.onclick = function(e) {
          e.preventDefault();
          const idx = container.children.length;
          container.appendChild(createQuestionRow(idx));
        };
      }

      container.onclick = function(e) {
        if (e.target.classList.contains('remove-screening-question')) {
          const row = e.target.closest('.screening-item');
          if (row) row.remove();
        }
      };

      container.onchange = function(e) {
        if (!e.target.classList.contains('question-type')) return;
        const row = e.target.closest('.screening-item');
        const num = row.querySelector('.number-field');
        const bool = row.querySelector('.boolean-field');
        if (e.target.value === 'number') {
          num.disabled = false; num.style.display = '';
          bool.disabled = true; bool.style.display = 'none';
        } else {
          num.disabled = true; num.style.display = 'none';
          bool.disabled = false; bool.style.display = '';
        }
      };
    }

    function createQuestionRow(idx) {
      const div = document.createElement('div');
      div.className = 'screening-item mb-3 row';
      div.setAttribute('data-index', idx);
      div.innerHTML = `
        <div class="col-md-5">
          <label>{{ __('Question') }}</label>
          <input type="text" name="screeningQuestions[${idx}][question_text]"
                 class="form-control" list="question-list" />
        </div>
        <div class="col-md-3">
          <label>{{ __('Type') }}</label>
          <select name="screeningQuestions[${idx}][question_type]"
                  class="form-select question-type">
            <option value="number">{{ __('Number') }}</option>
            <option value="boolean">{{ __('Yes / No') }}</option>
          </select>
        </div>
        <div class="col-md-2">
          <label>{{ __('Value') }}</label>
          <input type="number" name="screeningQuestions[${idx}][min_value]"
                 class="form-control min-field number-field" />
          <select name="screeningQuestions[${idx}][min_value]"
                  class="form-select min-field boolean-field"
                  disabled style="display:none;">
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
      `;
      return div;
    }

    document.addEventListener('DOMContentLoaded', initScreening);
    document.addEventListener('turbo:load', initScreening);
  })();
  </script>
</div>
