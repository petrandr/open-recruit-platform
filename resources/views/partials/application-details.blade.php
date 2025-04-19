<h5 class="mb-4">{{ $application->jobListing->title }} - {{ __('Application #') }}{{ $application->id }}</h5>
<dl class="row">
  <dt class="col-sm-4">{{ __('Applicant Name') }}</dt>
  <dd class="col-sm-8">{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</dd>
  <dt class="col-sm-4">{{ __('Email') }}</dt>
  <dd class="col-sm-8">{{ $application->candidate->email }}</dd>
  <dt class="col-sm-4">{{ __('Mobile Number') }}</dt>
  <dd class="col-sm-8">{{ $application->candidate->mobile_number }}</dd>
  <dt class="col-sm-4">{{ __('Job Title') }}</dt>
  <dd class="col-sm-8">{{ $application->jobListing->title }}</dd>
  <dt class="col-sm-4">{{ __('Job Type') }}</dt>
  <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $application->jobListing->job_type)) }}</dd>
  <dt class="col-sm-4">{{ __('Location') }}</dt>
  <dd class="col-sm-8">{{ $application->jobListing->location }}</dd>
  <dt class="col-sm-4">{{ __('Application Status') }}</dt>
  <dd class="col-sm-8">{{ ucfirst($application->status) }}</dd>
  <dt class="col-sm-4">{{ __('Notice Period') }}</dt>
  <dd class="col-sm-8">{{ $application->notice_period ?? '-' }}</dd>
  <dt class="col-sm-4">{{ __('Desired Salary') }}</dt>
  <dd class="col-sm-8">
    @if($application->desired_salary !== null)
      {{ number_format((float)$application->desired_salary, 2, ',', ' ') }} {{ $application->salary_currency }}
    @else
      -
    @endif
  </dd>
  <dt class="col-sm-4">{{ __('LinkedIn Profile') }}</dt>
  <dd class="col-sm-8">
    @if($application->linkedin_profile)
      <a href="{{ $application->linkedin_profile }}" target="_blank">{{ $application->linkedin_profile }}</a>
    @else
      -
    @endif
  </dd>
  <dt class="col-sm-4">{{ __('GitHub Profile') }}</dt>
  <dd class="col-sm-8">
    @if($application->github_profile)
      <a href="{{ $application->github_profile }}" target="_blank">{{ $application->github_profile }}</a>
    @else
      -
    @endif
  </dd>
  <dt class="col-sm-4">{{ __('How Did They Hear About Us?') }}</dt>
  <dd class="col-sm-8">{{ $application->how_heard ?? '-' }}</dd>
  <dt class="col-sm-4">{{ __('Submitted At') }}</dt>
  <dd class="col-sm-8">{{ $application->submitted_at->format('Y-m-d H:i:s') }}</dd>
</dl>
@if($application->jobListing->screeningQuestions->isNotEmpty())
  <hr />
  <h6>{{ __('Screening Questions') }}</h6>
  @foreach($application->jobListing->screeningQuestions as $question)
    <div class="mb-3">
      <strong>{{ $question->question_text ?? $question->question ?? 'Question' }}</strong>
      <p>{{ optional($application->answers->firstWhere('question_id', $question->id))->answer ?? '-' }}</p>
    </div>
  @endforeach
@endif
