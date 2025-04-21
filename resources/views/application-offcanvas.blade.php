<div class="offcanvas offcanvas-end" id="applicationOffcanvas" data-details-url-template="{{ route('platform.applications.details', ['application' => '__ID__']) }}" tabindex="-1" aria-labelledby="applicationOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="applicationOffcanvasLabel">{{ __('Application Details') }}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <p>{{ __('Select an application to view details.') }}</p>
<!-- Application details offcanvas script moved to resources/js/custom.js -->
  </div>
</div>
