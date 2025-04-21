<div class="modal fade" id="applicationCvModal" data-cv-url-template="{{ route('platform.applications.cv', ['application' => '__ID__']) }}" tabindex="-1" aria-labelledby="applicationCvModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="applicationCvModalLabel">{{ __('CV Preview') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="applicationCvModalBody">
        {{-- content loaded via AJAX --}}
      </div>
    </div>
  </div>
<!-- CV preview script moved to resources/js/custom.js -->
</div>
