@php
    use Orchid\Screen\Actions\Button;
    // Button to share application via ApplicationViewScreen::shareApplication
    $shareBtn = Button::make(__('Share'))
        ->icon('bs.send')
        ->action(route('platform.applications.view', [
            'application' => $application->id,
            'method'      => 'shareApplication',
        ]))
        ->form('screen-modal-form-shareModal')
        ->novalidate();
@endphp
<div class="d-flex justify-content-end mt-3">
    {!! $shareBtn->render() !!}
</div>