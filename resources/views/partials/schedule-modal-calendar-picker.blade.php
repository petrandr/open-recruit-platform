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

@push('scripts')
    <script> var application_calendar_route = '{{ route('platform.applications.calendars') }}';</script>
    <script src="{{ asset('js/interview_scheduler.js') }}" defer="" type="text/javascript" data-turbo-track="reload"></script>
@endpush

<script>


</script>

