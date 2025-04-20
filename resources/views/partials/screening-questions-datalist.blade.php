<datalist id="question-list"></datalist>
<script>
document.addEventListener('input', function(event) {
    // Only act on question inputs using the datalist
    const input = event.target;
    if (!input.matches('input[list="question-list"]')) {
        return;
    }
    const query = input.value;
    if (query.length < 2) {
        return;
    }
    fetch("{{ route('platform.screening-questions.search') }}?query=" + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('question-list');
            list.innerHTML = '';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                list.appendChild(option);
            });
        })
        .catch(err => console.error('Error fetching questions:', err));
});
</script>