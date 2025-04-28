<script>
    (function() {

        // Preload templates data as JSON array
        var templates = @json($templates);
        var select = document.getElementById('reject-template-select');
        var subjectInput = document.getElementById('reject-subject');
        var bodyTextarea = document.getElementById('reject-body');
        if (!select || !subjectInput || !bodyTextarea) {
            return;
        }
        select.addEventListener('change', function() {

            var id = this.value;
            var found = templates.find(function(t) {
                return t.id == id;
            });
            if (found) {
                subjectInput.value = found.subject;
                bodyTextarea.value = found.body;
            } else {
                subjectInput.value = '';
                bodyTextarea.value = '';
            }
        });
    })();
</script>
