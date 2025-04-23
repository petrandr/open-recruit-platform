@if($signedUrl)
    <p class="download-link text-center d-none">
        CV not displaying correctly? <a title="Download CV" class="text-primary" target="_blank" href="{{ $signedUrl }}">Click here</a> to download it.
    </p>
    <div id="cv-preview-container" style="text-align: center;">
        <p id="cv-loader">{{ __('Rendering document. Please wait...') }}</p>
        <div id="cv-content" data-url="{{ $signedUrl }}"></div>
    </div>
@else
    <p class="text-warning">{{ __('No CV available.') }}</p>
@endif
