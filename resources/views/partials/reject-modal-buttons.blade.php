@php
    use Orchid\Screen\Actions\Button;
    // Prepare action buttons
    $rejectOnly = Button::make(__('Reject Only'))
        ->icon('bs.x-circle')
        ->method('changeStatus', ['id' => $application->id, 'status' => 'rejected'])
        ->form('screen-modal-form-rejectModal')
        ->novalidate();
    $rejectSend = Button::make(__('Reject & Send'))
        ->icon('bs.send')
        ->method('rejectWithEmail', ['id' => $application->id])
        ->form('screen-modal-form-rejectModal')
        ->novalidate();
@endphp
<div class="d-flex justify-content-end mt-3">
    {{-- Reject Only button --}}
    {!! $rejectOnly->render() !!}
    {{-- Reject & Send button --}}
    {!! $rejectSend->render() !!}
</div>