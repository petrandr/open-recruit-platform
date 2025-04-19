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
document.addEventListener('DOMContentLoaded', function () {
  const rows = document.querySelectorAll('table tbody tr');
  const offcanvasEl = document.getElementById('applicationOffcanvas');
  const offcanvasBody = offcanvasEl.querySelector('.offcanvas-body');
  // URL template with placeholder __ID__ to be replaced
  const urlTemplate = '{{ route('platform.applications.details', ['application' => '__ID__']) }}';
  rows.forEach((tr) => {
    tr.style.cursor = 'pointer';
    tr.addEventListener('click', () => {
      const idCell = tr.querySelector('td');
      const id = idCell ? idCell.innerText.trim() : null;
      if (!id) {
        return;
      }
      const url = urlTemplate.replace('__ID__', id);
      fetch(url)
        .then((response) => response.text())
        .then((html) => {
          offcanvasBody.innerHTML = html;
          const off = new window.Bootstrap.Offcanvas(offcanvasEl);
          off.show();
        })
        .catch((err) => console.error('Failed to load application details:', err));
    });
  });
});
</script>
