@php
    use Orchid\Screen\Actions\Button;
    /** @var \App\Models\JobApplication $application */
    /** @var \Illuminate\Support\Collection $shared */
    /** @var \App\Models\User $user */
@endphp
<div class="p-4 py-4">
    <strong>{{ __('Already Shared With:') }}</strong>
    <ul class="list-group list-group-flush">
        @forelse($shared as $user)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    {{ $user->name }}
                    <small class="text-muted">({{ $user->email }})</small>
                </div>
                {!! Button::make('')
                    ->icon('bs.x-circle')
                    ->confirm(__('Remove share for :user?', ['user' => $user->name]))
                    ->method('removeShare', [
                        'id'      => $application->id,
                        'user_id' => $user->id,
                    ])
                    ->form('screen-modal-form-shareModal')
                    ->class('btn btn-sm btn-outline-danger')
                !!}
            </li>
        @empty
            <li class="list-group-item text-muted">
                {{ __('No users have been shared yet.') }}
            </li>
        @endforelse
    </ul>
</div>
