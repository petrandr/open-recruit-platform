@php
    use Orchid\Screen\Actions\Button;

    $scheduleSend = Button::make(__('Schedule & Send'))
        ->icon('bs.send')
        ->method('scheduleInterviewWithEmail', ['id' => $application->id])
        ->form('screen-modal-form-scheduleModal')
        ->novalidate();
    // The Reset button will be rendered manually in the markup below, as Orchid Button filters out custom onclick attributes.
@endphp

{{-- Confirmation checkbox container (hidden initially) --}}
<div id="confirm-checkbox-container" class="form-check mb-3 m-4" style="display: none;">
    <input class="form-check-input" type="checkbox" id="confirm-calendar-url" name="confirm_calendar_url">
    <label class="form-check-label" for="confirm-calendar-url">
        {{ __('I confirm that the calendar URL works.') }} <a id="calendar-link" href="#" target="_blank">
            <x-orchid-icon path="bs.box-arrow-up-right" class="overflow-visible"/>
        </a>
    </label>
</div>
<div class="d-flex justify-content-between mt-3">
    {{-- Left side: Reset button --}}
    <div>
        <button type="button" class="btn btn-link icon-link" onclick="resetScheduleModalForm(); return false;">
            <x-orchid-icon path="bs.arrow-counterclockwise" class="overflow-visible"/>
            {{ __('Reset Form') }}
        </button>
    </div>

    {{-- Right side: Schedule buttons with confirmation --}}
    <div class="d-flex">
        {{-- Hidden original send button --}}
        <span class="d-none">
            {!! $scheduleSend->id('schedule-send-hidden-btn')->render() !!}
        </span>
        {{-- Confirmation trigger button --}}
        <button type="button" id="schedule-send-confirm-btn" class="btn btn-link icon-link">
            <x-orchid-icon path="bs.send" class="overflow-visible"/>
            {{ __('Schedule & Send') }}
        </button>
    </div>
    <script>
        (function () {
            function confirmSchedule(event) {
                var calSelect = document.getElementById('schedule-calendar-select');
                var checkbox = document.getElementById('confirm-calendar-url');
                if (calSelect && !calSelect.disabled) {
                    checkbox.required = true;
                    var calId = calSelect.value;
                    const calendars = JSON.parse(calSelect.getAttribute('data-calendars'));
                    const calendar = calendars.find(c => c.id == calId);
                    if (calendar) {
                        document.getElementById('calendar-link').href = calendar['url'];
                    }

                    var checkboxContainer = document.getElementById('confirm-checkbox-container');


                    // First click: show confirmation checkbox
                    if (checkboxContainer.style.display === 'none') {
                        checkboxContainer.style.display = 'block';
                    }
                } else {
                    checkbox.required = false;
                }
                // Trigger the hidden original send button
                var hiddenBtn = document.getElementById('schedule-send-hidden-btn');
                if (hiddenBtn) {
                    hiddenBtn.click();
                } else {
                    var form = document.getElementById('screen-modal-form-scheduleModal');
                    if (form) {
                        form.submit();
                    }
                }
            }

                var btn = document.getElementById('schedule-send-confirm-btn');
                if (btn) {
                    btn.addEventListener('click', confirmSchedule);
                }

        })();
    </script>
</div>
