<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">{{ $application->jobListing->title }} - {{ __('Application #') }}{{ $application->id }}</h5>
  </div>
  <div class="card-body p-0">
    <table class="table table-borderless mb-0">
      <tbody>
        <tr>
          <th>{{ __('Applicant Name') }}</th>
          <td>{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</td>
        </tr>
        <tr>
          <th>{{ __('Email') }}</th>
          <td>{{ $application->candidate->email }}</td>
        </tr>
        <tr>
          <th>{{ __('Mobile Number') }}</th>
          <td>{{ $application->candidate->mobile_number }}</td>
        </tr>
        <tr>
          <th>{{ __('Job Type') }}</th>
          <td>{{ ucfirst(str_replace('_', ' ', $application->jobListing->job_type)) }}</td>
        </tr>
        <tr>
          <th>{{ __('Location') }}</th>
          <td>{{ $application->jobListing->location }}</td>
        </tr>
        <tr>
          <th>{{ __('Application Status') }}</th>
          <td>{{ ucfirst($application->status) }}</td>
        </tr>
        <tr>
          <th>{{ __('Notice Period') }}</th>
          <td>{{ $application->notice_period ?? '-' }}</td>
        </tr>
        <tr>
          <th>{{ __('Desired Salary') }}</th>
          <td>
            @if($application->desired_salary !== null)
              {{ number_format((float)$application->desired_salary, 2, ',', ' ') }} {{ $application->salary_currency }}
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <th>{{ __('LinkedIn Profile') }}</th>
          <td>
            @if($application->linkedin_profile)
              <a href="{{ $application->linkedin_profile }}" target="_blank">{{ $application->linkedin_profile }}</a>
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <th>{{ __('GitHub Profile') }}</th>
          <td>
            @if($application->github_profile)
              <a href="{{ $application->github_profile }}" target="_blank">{{ $application->github_profile }}</a>
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <th>{{ __('How Did They Hear About Us?') }}</th>
          <td>{{ $application->how_heard ?? '-' }}</td>
        </tr>
        <tr>
          <th>{{ __('Submitted At') }}</th>
          <td>{{ $application->submitted_at->format('Y-m-d H:i:s') }}</td>
        </tr>
        <tr>
          <th>{{ __('CV') }}</th>
          <td><a href="#">{{ __('View CV') }}</a></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
#if($application->jobListing->screeningQuestions->isNotEmpty())
  <div class="card">
    <div class="card-header">
      <h6 class="mb-0">{{ __('Screening Questions') }}</h6>
    </div>
    <div class="card-body">
      @foreach($application->jobListing->screeningQuestions as $question)
        <dl class="row mb-3">
          <dt class="col-sm-4">{{ $question->question_text ?? $question->question }}</dt>
          <dd class="col-sm-8">{{ optional($application->answers->firstWhere('question_id', $question->id))->answer ?? '-' }}</dd>
        </dl>
      @endforeach
    </div>
  </div>
@endif
