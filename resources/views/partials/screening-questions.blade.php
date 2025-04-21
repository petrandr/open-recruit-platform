<fieldset class="mb-3 screening-questions">
    <div class="col p-0 px-3">
        <legend class="text-body-emphasis mt-2 mx-2">
            {{ __('Screening Questions') }}
        </legend>
    </div>
    <dl class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
        @foreach($application->jobListing->screeningQuestions as $question)
            <div class="row {{ $loop->first ? '' : 'border-top' }}">
                <div class="col-10 py-3 text-muted">
                    {!! $question->question_text ?? $question->question !!}
                </div>
                <div class="col-2 py-3">
                    {!! optional($application->answers->firstWhere('question_id', $question->id))->answer_text ?? '-' !!}
                </div>
            </div>
        @endforeach
    </dl>
</fieldset>
