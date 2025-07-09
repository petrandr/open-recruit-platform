@php
    use Orchid\Screen\Actions\Button;
    // Prepare action buttons with explicit routes to avoid including filter query parameters
    $rejectOnly = Button::make(__('Reject without Email'))
        ->icon('bs.x-circle')
        ->action(route('platform.applications', [
            'method' => 'changeStatus',
            'id'     => $application->id,
            'status' => 'rejected',
        ]))
        ->form('screen-modal-form-rejectModal')
        ->novalidate();
    $rejectSend = Button::make(__('Reject & Send'))
        ->icon('bs.send')
        ->action(route('platform.applications', [
            'method' => 'rejectWithEmail',
            'id'     => $application->id,
        ]))
        ->form('screen-modal-form-rejectModal');
@endphp
<div class="d-flex justify-content-end mt-3">
    {{-- Reject Only button --}}
    {!! $rejectOnly->render() !!}
    {{-- Reject & Send button --}}
    {!! $rejectSend->render() !!}
</div>
