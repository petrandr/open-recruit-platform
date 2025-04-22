<div>
    @php $status = $status ?? null; @endphp
    @if($status === 'disable')
        <div class="alert alert-danger mb-4" role="alert">
            <strong>{{ __('Danger') }}:</strong>
            {{ __('This job position has been disabled.') }}
        </div>
    @elseif($status === 'inactive')
        <div class="alert alert-warning mb-4" role="alert">
            <strong>{{ __('Warning') }}:</strong>
            {{ __('This job position is inactive.') }}
        </div>
    @endif
</div>