// Centralized JavaScript for application behavior
// Centralized JavaScript for modal, offcanvas, and comment behavior
(function() {
  // Spinner HTML for offcanvas loading
  const spinnerHtml =
    '<div class="d-flex justify-content-center align-items-center" style="height:100%;">' +
      '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>' +
    '</div>';

  // Initialize all custom behaviors
  function initCustom() {
    bindCommentEnter();
    bindCvModal();
    bindOffcanvas();
  }

  // Bind Enter key to submit comments
  function bindCommentEnter() {
    const textarea = document.querySelector('textarea[name="comment_text"]');
    if (!textarea || textarea._enterBound) return;
    textarea._enterBound = true;
    textarea.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const btn = document.querySelector('button[data-method="addComment"]');
        if (btn) btn.click();
      }
    });
  }

  // Bind Bootstrap modal show for CV preview
  function bindCvModal() {
    const modal = document.getElementById('applicationCvModal');
    if (!modal || modal._modalBound) return;
    modal._modalBound = true;
    modal.addEventListener('show.bs.modal', function(e) {
      const trigger = e.relatedTarget;
      if (!trigger) return;
      const id = trigger.getAttribute('data-application-id');
      const body = modal.querySelector('#applicationCvModalBody');
      body.innerHTML = '<p>Rendering document. Please wait...</p>';
      const template = modal.getAttribute('data-cv-url-template');
      const url = template.replace('__ID__', id);
      fetch(url)
        .then(resp => resp.text())
        .then(html => handleCvHtml(body, html))
        .catch(err => { body.innerHTML = `<p class=\"text-danger\">${err.message||err}</p>`; });
    });
  }

  function handleCvHtml(body, html) {
    body.innerHTML = html;
    const loader = body.querySelector('#cv-loader');
    const content = body.querySelector('#cv-content');
    if (loader && content && content.dataset.url) {
      loader.style.display = '';
      content.innerHTML = '';
      const iframe = document.createElement('iframe');
      iframe.style.width = '100%'; iframe.style.height = '800px';
      iframe.frameBorder = '0';
      iframe.src = 'https://docs.google.com/gview?url=' +
        encodeURIComponent(content.dataset.url) + '&embedded=true';
      iframe.onload = () => { loader.style.display = 'none'; iframe.style.display = 'block'; };
      content.appendChild(iframe);
    }
  }

  // Bind offcanvas click handlers
  function bindOffcanvas() {

    if (window._offcanvasBound) return;
    window._offcanvasBound = true;
    document.addEventListener('click', function(event) {

      // Find the closest element node
      let el = event.target;
      while (el && el.nodeType !== Node.ELEMENT_NODE) {
        el = el.parentNode;
      }
      if (!el) return;
      const trigger = el.closest('.application-offcanvas-trigger');
      if (!trigger) return;
      const offcanvasEl = document.getElementById('applicationOffcanvas');
      if (!offcanvasEl) return;

      const id = trigger.getAttribute('data-id');
      if (!id) return;

      const urlTemplate = offcanvasEl.getAttribute('data-details-url-template');
      const url = urlTemplate.replace('__ID__', id);
      const off = new window.Bootstrap.Offcanvas(offcanvasEl);

      off.show();
      const body = offcanvasEl.querySelector('.offcanvas-body');
      body.innerHTML = spinnerHtml;
      fetch(url)
        .then(r => r.text())
        .then(html => { console.log(body); body.innerHTML = html; })
        .catch(err => { body.innerHTML = '<p class="text-danger">Failed to load details.</p>'; });
    });
  }

  // Run on full page load and after Turbo navigations
  document.addEventListener('DOMContentLoaded', initCustom);
  document.addEventListener('turbo:load', initCustom);
  // Initial binding in case DOMContentLoaded has already fired
  initCustom();
})();
