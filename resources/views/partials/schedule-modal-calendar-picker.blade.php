@php
    use Orchid\Screen\Fields\Relation;
    use Orchid\Screen\Fields\Select;
    use App\Models\User;
@endphp
<div id="calendar-picker" class="bg-white rounded shadow-sm p-4 py-0 flex-column gap-3" style="display: none;">
    <hr>
    <div class="mb-3">
        {{-- User picker (async search) --}}
        {!! Relation::make('user_id')
            ->id('schedule-user-select')
            ->fromModel(User::class, 'name')
            ->searchColumns('name', 'email')
            ->title(__('Select Interviewer'))
            ->placeholder(__('Select interviewer'))
             !!}
    </div>
    <div class="mb-3">
        {!! Select::make('calendar_id')
            ->id('schedule-calendar-select')
            ->class('form-control')
            ->title(__('Select Calendar'))
            ->placeholder(__('Select interviewer'))
            ->disabled()
        !!}
    </div>
</div>
<script>
;(function() {
    function initSchedulePicker() {
        var placeholder = '@{{appointment_calendar}}';
        var bodyEl      = document.getElementById('schedule-body');
        var picker      = document.getElementById('calendar-picker');
        var userSelect  = document.getElementById('schedule-user-select');
        var calSelect   = document.getElementById('schedule-calendar-select');
        // If TomSelect is used on the calendar select, disable its default alphabetical sorting
        var tsCal = calSelect && (calSelect.tomselect || calSelect.tomSelect);
        if (tsCal) {
            var calCfg = tsCal.settings || tsCal.config;
            if (calCfg) {
                calCfg.sortField = [{ field: '$order', direction: 'asc' }];
            }
        }
        // If TomSelect is used on the user select, disable default sorting so placeholder remains at top
        var tsUser = userSelect && (userSelect.tomselect || userSelect.tomSelect);
        if (tsUser) {
            var userCfg = tsUser.settings || tsUser.config;
            if (userCfg) {
                userCfg.sortField = [{ field: '$order', direction: 'asc' }];
            }
        }
        if (!bodyEl || !picker || !userSelect || !calSelect) {
            return;
        }
        // Hold fetched calendars
        var calendars = [];
        // Helpers for TomSelect or native select
        function clearCalOptions() {
            var ts = calSelect.tomselect || calSelect.tomSelect;
            if (ts && typeof ts.clearOptions === 'function') {
                ts.clearOptions();
            } else {
                calSelect.innerHTML = '';
            }
        }
        function addCalOption(value, text) {
            var ts = calSelect.tomselect || calSelect.tomSelect;
            if (ts && typeof ts.addOption === 'function') {
                ts.addOption({ value: value, text: text });
            } else {
                var o = document.createElement('option');
                o.value = value;
                o.text = text;
                calSelect.appendChild(o);
            }
        }
        function setCalDisabled(disabled) {
            var ts = calSelect.tomselect || calSelect.tomSelect;
            if (ts) {
                if (disabled && typeof ts.disable === 'function') {
                    ts.disable();
                } else if (!disabled && typeof ts.enable === 'function') {
                    ts.enable();
                }
            }
            calSelect.disabled = disabled;
        }
        function togglePicker() {
            picker.style.display = bodyEl.value.includes(placeholder) ? '' : 'none';
            if (picker.style.display !== 'none') {
                userSelect.required = true;
                calSelect.required = true;
            } else {
                userSelect.required = false;
                calSelect.required = false;
            }
        }
        // Wire up events
        bodyEl.addEventListener('input', togglePicker);
        togglePicker();
        userSelect.addEventListener('change', function() {
            var uid = this.value;
            clearCalOptions();
            addCalOption('', '{{ __('Select calendar') }}');
            setCalDisabled(true);
            calendars = [];
            if (!uid) {
                return;
            }
            fetch('{{ route('platform.applications.calendars') }}?user_id=' + uid)
                .then(resp => resp.json())
                .then(function(data) {
                    calendars = data;
                    // Store calendar data for confirmation dialog
                    if (calSelect) {
                        calSelect.dataset.calendars = JSON.stringify(data);
                    }
                    data.forEach(function(c) {
                        addCalOption(c.id, c.name);
                    });
                    setCalDisabled(false);
                });
        });
        calSelect.addEventListener('change', function() {
            var calId = this.value;
            var cal = calendars.find(function(c) { return c.id == calId; });
            if (cal && bodyEl.value.includes(placeholder)) {
                bodyEl.value = bodyEl.value.replace(placeholder, cal.url);
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSchedulePicker);
    } else {
        initSchedulePicker();
    }
    document.addEventListener('turbo:load', initSchedulePicker);
})();
</script>
