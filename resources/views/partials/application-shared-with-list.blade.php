@php
    /** @var \Illuminate\Support\Collection $shared */
    /** @var \App\Models\User $user */
@endphp
<div class="p-3">
    @if($shared->isEmpty())
        <div class="text-muted">{{ __('Not shared with any users.') }}</div>
    @else
        <ul class="list-group list-group-flush">
            @foreach($shared as $user)
                <li class="list-group-item">
                    {{ $user->name }}
                    <small class="text-muted">({{ $user->email }})</small>
                </li>
            @endforeach
        </ul>
    @endif
</div>