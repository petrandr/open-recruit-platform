<script>
    (function () {

        // Preload templates data as JSON array
        var templates = @json($templates);
        var select = document.getElementById('reject-template-select');
        var subjectInput = document.getElementById('reject-subject');
        var bodyTextarea = document.getElementById('reject-body');
        if (!select || !subjectInput || !bodyTextarea) {
            return;
        }
        select.addEventListener('change', function () {

            var id = this.value;
            var found = templates.find(function (t) {
                return t.id == id;
            });

            subjectInput.value = found ? found.subject : '';
            if (bodyTextarea.matches('.js-ckeditor')) {
                CK_EDITORS.get(bodyTextarea.id).setData(found ? found.body : '');
            } else {
                bodyTextarea.value = found ? found.body : '';
            }
        });
    })();
</script>
