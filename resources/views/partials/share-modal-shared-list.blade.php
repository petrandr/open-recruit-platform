@php
    /** @var \Illuminate\Support\Collection $shared */
    /** @var \App\Models\User $user */
@endphp
<div class="p-4 py-4">
    <strong>{{ __('Already Shared With:') }}</strong>
    <ul class="list-group list-group-flush">
        @forelse($shared as $user)
            <li class="list-group-item">
                {{ $user->name }}
                <small class="text-muted">({{ $user->email }})</small>
            </li>
        @empty
            <li class="list-group-item text-muted">
                {{ __('No users have been shared yet.') }}
            </li>
        @endforelse
    </ul>
</div>
