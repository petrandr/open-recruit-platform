// Centralized JavaScript for application behavior
// Centralized JavaScript for modal, and comment behavior
(function () {

    // Initialize all custom behaviors
    function initCustom() {
        console.log('load');
        bindCommentEnter();
        bindCvModal();
        bindScreeningQuestions();
        bindScheduleTemplateSelect();
        bindCKEditor();
    }

    function bindCKEditor() {
        document.querySelectorAll('.js-ckeditor').forEach(el => {
            // skip if already initialized
            if (el.dataset.ckeditorInitialized) return;

            ClassicEditor.create(el)
                .then(editor => {
                    // mark as done so we don't re‐init on turbo:load
                    el.dataset.ckeditorInitialized = 'true';
                    // remove the inline "display:none" that CKEditor just applied
                    el.style.removeProperty('display');
                    // add your utility class instead
                    el.classList.add('d-none');
                })
                .catch(console.error);
        });
    }

    // Bind Enter key to submit comments
    function bindCommentEnter() {
        const textarea = document.querySelector('textarea.comment-textarea');
        if (!textarea || textarea._enterBound) return;
        textarea._enterBound = true;
        textarea.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const btn = document.querySelector('button.comment-submit');
                if (btn) btn.click();
            }
        });
    }

    // Bind Bootstrap modal show for CV preview
    function bindCvModal() {
        const modal = document.getElementById('applicationCvModal');
        if (!modal || modal._modalBound) return;
        modal._modalBound = true;
        modal.addEventListener('show.bs.modal', function (e) {
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
                .catch(err => {
                    body.innerHTML = `<p class=\"text-danger\">${err.message || err}</p>`;
                });
        });
    }

    function handleCvHtml(body, html) {
        body.innerHTML = html;
        const loader = body.querySelector('#cv-loader');
        const content = body.querySelector('#cv-content');
        if (loader && content && content.dataset.url) {
            function createIframe() {
                var iframe = document.createElement('iframe');
                iframe.id = 'docFrame';
                iframe.style.width = "100%";
                iframe.style.height = "800px";
                iframe.frameBorder = "0";
                iframe.style.display = "none";
                content.append(iframe);
                return iframe;
            }

            // Function to set (or reset) the iframe source
            function loadIframe() {
                var iframe = document.getElementById('docFrame') || createIframe();
                iframe.src = 'https://docs.google.com/gview?url=' + encodeURIComponent(content.dataset.url) + '&embedded=true';
                console.log(iframe.src);
                return iframe;
            }

            var maxRetries = 5;
            var retryInterval = 3000; // in milliseconds
            var retryCount = 0;
            var isLoaded = false;

            // Load the iframe the first time
            var iframe = loadIframe();

            // Attach an onload event listener to the iframe
            iframe.onload = function () {
                isLoaded = true;
                console.log("Iframe loaded successfully.");
                document.getElementById('cv-loader').style.display = 'none';
                iframe.style.display = 'block';
                document.getElementsByClassName('download-link')[0].classList.remove('d-none');
            };

            // Function to retry loading the iframe if not loaded
            function retryLoad() {
                if (!isLoaded && retryCount < maxRetries) {
                    retryCount++;
                    console.log("Retrying load: attempt " + retryCount);
                    // Reassign the same source to trigger a reload
                    iframe.src = iframe.src;
                    // Schedule the next check after the retry interval
                    setTimeout(retryLoad, retryInterval);
                } else if (!isLoaded) {
                    document.getElementById('cv-loader').textContent = 'Unable to load the file.'
                    console.log("Max retries reached. Iframe did not load successfully.");
                    document.getElementsByClassName('download-link')[0].classList.remove('d-none');
                }
            }

            // Start retrying after the initial delay
            setTimeout(retryLoad, retryInterval);
        }
    }

    // Bind dynamic screening questions add/remove/type logic
    function bindScreeningQuestions() {
        const container = document.getElementById('screening-questions-container');
        const addBtn = document.getElementById('add-screening-question');
        if (!container || !addBtn || container._screeningBound) return;
        container._screeningBound = true;
        // Determine next index
        const items = container.querySelectorAll('.screening-item');
        let nextIndex = items.length ? (parseInt(items[items.length - 1].dataset.index) + 1) : 0;
        // Add new question
        addBtn.addEventListener('click', function () {
            const idx = nextIndex++;
            const template = document.getElementById('screening-question-template');
            let html = template.innerHTML.replace(/__INDEX__/g, idx);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            // Append new screening item
            container.appendChild(wrapper.firstElementChild);
        });
        // Remove question
        container.addEventListener('click', function (e) {
            if (e.target.matches('.remove-screening-question')) {
                e.preventDefault();
                const row = e.target.closest('.screening-item');
                if (row) row.remove();
            }
        });
        // Toggle min_value fields by type
        container.addEventListener('change', function (e) {
            if (e.target.matches('.question-type')) {
                const row = e.target.closest('.screening-item');
                const type = e.target.value;
                const numberField = row.querySelector('.number-field');
                const booleanField = row.querySelector('.boolean-field');
                if (type === 'number') {
                    numberField.disabled = false;
                    numberField.style.display = '';
                    booleanField.disabled = true;
                    booleanField.style.display = 'none';
                } else {
                    numberField.disabled = true;
                    numberField.style.display = 'none';
                    booleanField.disabled = false;
                    booleanField.style.display = '';
                }
            }
        });
    }

    // Bind schedule template selection to populate subject/body
    function bindScheduleTemplateSelect() {
        var select = document.getElementById('schedule-template-select');
        if (!select || select._bound) return;
        select._bound = true;
        var templates = [];
        try {
            templates = JSON.parse(select.getAttribute('data-templates') || '[]');
        } catch (e) {
            console.error('Failed to parse schedule templates JSON', e);
        }
        var subjectInput = document.getElementById('schedule-subject');
        var bodyTextarea = document.getElementById('schedule-body');
        select.addEventListener('change', function () {
            var found = templates.find(function (t) {
                return t.id == select.value;
            });
            if (subjectInput) subjectInput.value = found ? found.subject : '';
            if (bodyTextarea) {
                bodyTextarea.value = found ? found.body : '';
                bodyTextarea.dispatchEvent(new Event('input'));
            }
        });
    }

    // Reset schedule modal form
    window.resetScheduleModalForm = function () {
        var form = document.getElementById('screen-modal-form-scheduleModal');
        if (!form) return;
        form.reset();
        // Clear template select
        var templateSelect = document.getElementById('schedule-template-select');
        if (templateSelect) {
            templateSelect.value = '';
            // Trigger change so subject/body clear too
            templateSelect.dispatchEvent(new Event('change'))
        }
        // Clear dynamic elements
        var bodyEl = document.getElementById('schedule-body');
        if (bodyEl) {
            bodyEl.value = '';
            bodyEl.dispatchEvent(new Event('input'));
        }

        var subjectInput = document.getElementById('schedule-subject');
        if (subjectInput) subjectInput.value = '';
        var userSelect = document.getElementById('schedule-user-select');
        if (userSelect) {
            userSelect.value = '';
            const event = new Event('change', {bubbles: true});
            userSelect.dispatchEvent(event);
        }
        var calSelect = document.getElementById('schedule-calendar-select');
        if (calSelect) {
            calSelect.innerHTML = '<option value="">' + (calSelect.querySelector('option')?.textContent || '') + '</option>';
            calSelect.disabled = true;
        }
    };

    // Placeholder insertion function
    window.appendPlaceholderToTextarea = function (placeholder) {
        var textarea = document.getElementById('template-body');
        if (!textarea) {
            return;
        }
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;
        textarea.value = text.substring(0, start) + placeholder + text.substring(end);
        var newPos = start + placeholder.length;
        textarea.setSelectionRange(newPos, newPos);
        textarea.focus();
    };

    // Run on full page load and after Turbo navigations
    document.addEventListener('DOMContentLoaded', initCustom);
    document.addEventListener('turbo:load', initCustom);
    // Initial binding in case DOMContentLoaded has already fired
    // initCustom();
})();
