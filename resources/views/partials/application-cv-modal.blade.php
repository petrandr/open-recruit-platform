<div class="modal fade" id="applicationCvModal" tabindex="-1" aria-labelledby="applicationCvModalLabel" aria-hidden="true">
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
</div>
<script>
</script>
<script>
  // Handle modal show event to fetch and display CV
  document.getElementById('applicationCvModal').addEventListener('show.bs.modal', function (e) {
    const trigger = e.relatedTarget;
    if (!trigger) {
      return;
    }
    const id = trigger.getAttribute('data-application-id');
    const body = this.querySelector('#applicationCvModalBody');
    body.innerHTML = '<p>{{ __('Rendering document. Please wait...') }}</p>';
    // Build CV URL via named route
    const template = '{{ route("platform.applications.cv", ["application" => "__ID__"]) }}';
    const url = template.replace('__ID__', id);
    fetch(url)
      .then(response => response.text())
      .then(html => {
        body.innerHTML = html;
        // After injecting partial, preview via Google Docs iframe
        const loader = body.querySelector('#cv-loader');
        const content = body.querySelector('#cv-content');
        if (content && content.dataset.url) {
          loader.style.display = '';
          content.innerHTML = '';
          const iframe = document.createElement('iframe');
          iframe.style.width = '100%';
          iframe.style.height = '800px';
          iframe.frameBorder = '0';
          iframe.src = 'https://docs.google.com/gview?url=' + encodeURIComponent(content.dataset.url) + '&embedded=true';
          iframe.onload = function() {
            loader.style.display = 'none';
            iframe.style.display = 'block';
          };
          content.appendChild(iframe);
        }
      })
      .catch(err => {
        body.innerHTML = `<p class="text-danger">${err.message || err}</p>`;
      });
  });
</script>
</script>
