const CK_EDITORS = new Map();

((async function () {
    // Prevent double-init on the same “page”
    let inited = false;

    // Initialize all custom behaviors
    async function initCustom() {
        // if (inited) return;
        // inited = true;

        try {
            // Run async binds in parallel
            await Promise.all([
                bindCKEditor(),
                bindCommentEnter(),
                bindCvModal(),
                // Re-bind CKEditor on dynamic modal content
                bindModalsCKEditor(),
                bindScreeningQuestions(),
                bindScheduleTemplateSelect()
            ]);
        } catch (e) {
            console.error('Error initializing custom behaviors:', e);
        }


        const init_load = new CustomEvent('init:load', {
            detail: {
                ckeditors: CK_EDITORS
            },
            bubbles: true
        });
        document.dispatchEvent(init_load);
    }

    // Async CKEditor binding
    async function bindCKEditor() {
        const els = Array.from(document.querySelectorAll('.js-ckeditor'))
            .filter(el => !el.dataset.ckeditorInitialized);

        await Promise.all(els.map(async el => {
            try {
                const editor = await ClassicEditor.create(el);
                el.dataset.ckeditorInitialized = 'true';
                el.style.removeProperty('display');
                el.classList.add('d-none');
                CK_EDITORS.set(el.id, editor);
            } catch (e) {
                console.error('CKEditor init failed for:', el, e);
            }
        }));

        document.dispatchEvent(new CustomEvent('ckeditors:render', {
            detail: {ckeditors: CK_EDITORS},
            bubbles: true
        }));
    }

    // Other binds need not be async but we return a resolved Promise
    function bindCommentEnter() {
        return new Promise(resolve => {
            const textarea = document.querySelector('textarea.comment-textarea');
            if (textarea && !textarea._enterBound) {
                textarea._enterBound = true;
                textarea.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const btn = document.querySelector('button.comment-submit');
                        if (btn) btn.click();
                    }
                });
            }
            resolve();
        });
    }

    function bindCvModal() {
        return new Promise(resolve => {
            const modal = document.getElementById('applicationCvModal');
            console.log(modal);
            if (modal && !modal._modalBound) {
                modal._modalBound = true;
                modal.addEventListener('show.bs.modal', async function (e) {
                    const trigger = e.relatedTarget;
                    if (!trigger) return;
                    const id = trigger.getAttribute('data-application-id');
                    const body = modal.querySelector('#applicationCvModalBody');
                    body.innerHTML = '<p>Rendering document. Please wait...</p>';
                    const template = modal.getAttribute('data-cv-url-template');
                    const url = template.replace('__ID__', id);
                    try {
                        const resp = await fetch(url);
                        const html = await resp.text();
                        handleCvHtml(body, html);
                    } catch (err) {
                        body.innerHTML = `<p class=\"text-danger\">${err.message || err}</p>`;
                    }
                });
            }
            resolve();
        });
    }

    // handleCvHtml remains unchanged
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

    function bindScreeningQuestions() {
        return new Promise(resolve => {
            const container = document.getElementById('screening-questions-container');
            const addBtn = document.getElementById('add-screening-question');
            if (container && addBtn && !container._screeningBound) {
                container._screeningBound = true;
                let nextIndex = container.querySelectorAll('.screening-item').length;
                addBtn.addEventListener('click', function () {
                    const idx = nextIndex++;
                    const tpl = document.getElementById('screening-question-template');
                    const html = tpl.innerHTML.replace(/__INDEX__/g, idx);
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    container.appendChild(wrapper.firstElementChild);
                });
                container.addEventListener('click', e => {
                    if (e.target.matches('.remove-screening-question')) {
                        e.target.closest('.screening-item')?.remove();
                    }
                });
                container.addEventListener('change', e => {
                    if (e.target.matches('.question-type')) {
                        const row = e.target.closest('.screening-item');
                        const type = e.target.value;
                        const numF = row.querySelector('.number-field');
                        const boolF = row.querySelector('.boolean-field');
                        if (type === 'number') {
                            numF.disabled = false;
                            numF.style.display = '';
                            boolF.disabled = true;
                            boolF.style.display = 'none';
                        } else {
                            numF.disabled = true;
                            numF.style.display = 'none';
                            boolF.disabled = false;
                            boolF.style.display = '';
                        }
                    }
                });
            }
            resolve();
        });
    }

    function bindScheduleTemplateSelect() {
        return new Promise(resolve => {
            const select = document.getElementById('schedule-template-select');
            if (select && !select._bound) {
                select._bound = true;
                let templates = [];
                try {
                    templates = JSON.parse(select.getAttribute('data-templates') || '[]');
                } catch {
                }
                const subj = document.getElementById('schedule-subject');
                const body = document.getElementById('schedule-body');
                select.addEventListener('change', function () {
                    const found = templates.find(t => t.id == select.value) || {};
                    if (subj) subj.value = found.subject || '';
                    if (body) {
                        body.value = found.body || '';
                        body.dispatchEvent(new Event('input'));
                    }
                });
            }
            resolve();
        });
    }

    /**
     * Bind CKEditor initialization on dynamically loaded modals
     */
    function bindModalsCKEditor() {
        return new Promise(resolve => {
            document.querySelectorAll('.modal').forEach(modal => {
                if (!modal._ckEditorBound) {
                    modal._ckEditorBound = true;
                    modal.addEventListener('shown.bs.modal', function () {
                        bindCKEditor();
                    });
                }
            });
            resolve();
        });
    }

    // Run on full page load and after Turbo navigations
    document.addEventListener('turbo:load', initCustom);
    document.addEventListener('DOMContentLoaded', initCustom);

})());

