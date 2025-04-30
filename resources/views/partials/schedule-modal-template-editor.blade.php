<script>
    (function() {
        var templates = @json($templates);
        document.addEventListener('change', function(e) {
            var select = e.target;
            if (select.id !== 'schedule-template-select') {
                return;
            }
            var subjectInput = document.getElementById('schedule-subject');
            var bodyTextarea = document.getElementById('schedule-body');
            if (!subjectInput || !bodyTextarea) {
                return;
            }
            var found = templates.find(function(t) { return t.id == select.value; });
            subjectInput.value = found ? found.subject : '';
            if (bodyTextarea.matches('.js-ckeditor')) {
                CK_EDITORS.get(bodyTextarea.id).setData(found ? found.body : '');
            } else {
                bodyTextarea.value = found ? found.body : '';
            }
            // Trigger calendar picker update
            bodyTextarea.dispatchEvent(new Event('input'));
        });
    })();
</script>
