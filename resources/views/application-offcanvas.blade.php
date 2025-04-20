<div class="offcanvas offcanvas-end" tabindex="-1" id="applicationOffcanvas" aria-labelledby="applicationOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="applicationOffcanvasLabel">{{ __('Application Details') }}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <p>{{ __('Select an application to view details.') }}</p>
  </div>
</div>

<script>
(function() {
  // Offcanvas elements and URL template
  const offcanvasEl = document.getElementById('applicationOffcanvas');
  const offcanvasBody = offcanvasEl.querySelector('.offcanvas-body');
  const urlTemplate = '{{ route("platform.applications.details", ['application' => '__ID__']) }}';
  // Spinner placeholder while loading
  const spinnerHtml = `
    <div class="d-flex justify-content-center align-items-center" style="height:100%;">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">{{ __('Loading...') }}</span>
      </div>
    </div>
  `;

  // Delegate clicks on candidate name triggers
  document.addEventListener('click', function(event) {
    const trigger = event.target.closest('.application-offcanvas-trigger');
    if (!trigger) {
      return;
    }
    const id = trigger.getAttribute('data-id');
    if (!id) {
      return;
    }
    const url = urlTemplate.replace('__ID__', id);
    // Show offcanvas with spinner
    const off = new window.Bootstrap.Offcanvas(offcanvasEl);
    off.show();
    offcanvasBody.innerHTML = spinnerHtml;
    // Load content
    fetch(url)
      .then(function(response) { return response.text(); })
      .then(function(html) {
        offcanvasBody.innerHTML = html;
      })
      .catch(function(err) {
        console.error('Failed to load application details:', err);
        offcanvasBody.innerHTML = '<p class="text-danger">{{ __('Failed to load details.') }}</p>';
      });
  });
})();
</script>
