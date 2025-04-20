@if($signedUrl)
    <div id="cv-preview-container" style="text-align: center;">
        <p id="cv-loader">{{ __('Rendering document. Please wait...') }}</p>
        <div id="cv-content" data-url="{{ $signedUrl }}"></div>
    </div>
@else
    <p class="text-warning">{{ __('No CV available.') }}</p>
@endif