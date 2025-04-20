<div>
  <dl class="row mb-4">
    <dt class="col-sm-4">{{ __('Applicant') }}</dt>
    <dd class="col-sm-8">{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</dd>

    <dt class="col-sm-4">{{ __('Position') }}</dt>
    <dd class="col-sm-8">{{ $application->jobListing->title }}</dd>

    <dt class="col-sm-4">{{ __('Location') }}</dt>
    <dd class="col-sm-8">{{ ucfirst($application->jobListing->location) }}</dd>

    <dt class="col-sm-4">{{ __('Desired Salary') }}</dt>
    <dd class="col-sm-8">
      @if($application->desired_salary !== null)
        @php
          $amt = (float)$application->desired_salary;
          $formatted = number_format($amt, 2, ',', '.');
          $code = strtoupper($application->salary_currency ?? '');
          $symbol = match ($code) {
              'EUR' => '€',
              'USD' => '$',
              'GBP' => '£',
              default => $code,
          };
        @endphp
        {{ $symbol . $formatted }}
      @else
        -
      @endif
    </dd>

    <dt class="col-sm-4">{{ __('Status') }}</dt>
    <dd class="col-sm-8">
      <span class="badge bg-{{ match($application->status) {
          'accepted' => 'success',
          'rejected' => 'danger',
          'under review' => 'warning',
          default => 'secondary',
      } }} status-badge">{{ ucfirst($application->status) }}</span>
    </dd>

    <dt class="col-sm-4">{{ __('Submitted') }}</dt>
    <dd class="col-sm-8">{{ $application->submitted_at->format('Y-m-d') }}</dd>
  </dl>

    @if($application->jobListing->screeningQuestions->isNotEmpty())
    <h6 class="mb-2">{{ __('Screening Questions') }}</h6>
    <dl class="row mb-4">
      @foreach($application->jobListing->screeningQuestions as $question)
        <dt class="col-sm-10">{{ $question->question_text ?? $question->question }}</dt>
        <dd class="col-sm-2">{{ optional($application->answers->firstWhere('question_id', $question->id))->answer_text ?? '-' }}</dd>
      @endforeach
    </dl>
  @endif

  <div class="d-grid">
    <a href="{{ route('platform.applications.view', $application->id) }}" class="btn btn-primary">
      {{ __('View Details') }}
    </a>
  </div>
</div>
