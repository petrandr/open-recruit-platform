@if($application->comments->isNotEmpty())
  <div class="card mb-4">
    <div class="card-header">
      <h6 class="mb-0">{{ __('Comments') }}</h6>
    </div>
    <div class="card-body">
      @foreach($application->comments as $comment)
        <div class="mb-3">
          <small class="text-muted">
            {{ $comment->created_at->format('Y-m-d H:i') }} –
            {{ optional($comment->user)->name ?: ucfirst($comment->source) }}
          </small>
          <p class="mt-1">{{ $comment->comment_text }}</p>
        </div>
      @endforeach
    </div>
  </div>
@endif