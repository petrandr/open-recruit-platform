@php
    /**
     * Partial to render application status history as a list of badges with date and user.
     * Expects $statusLogs to be a collection of ApplicationStatusLog models.
     */
    $statuses = \App\Support\ApplicationStatus::all();
@endphp
<div class="card bg-white rounded">
    <div class="card-body">
        @php
            $meta = $statuses['submitted'];
            $label = $meta['label'];
            $color = $meta['color'];
            $date = $created_at->format('M jS Y');
        @endphp
        <div class="d-flex align-items-center mb-4">
            <span class="me-2">{{ $date }}</span>
            <span class="badge bg-{{ $color }} me-2">{{ $label }}</span>
        </div>
        @foreach($statusLogs as $log)
            @php
                $meta = $statuses[$log->to_status] ?? [];
                $label = $meta['label'];
                $color = $meta['color'] ?? 'secondary';
                $date = $log->created_at->format('M jS Y');
            @endphp
            <div class="d-flex align-items-center mb-4">
                <span class="me-2">{{ $date }}</span>
                <span class="badge bg-{{ $color }} me-2">{{ $label }}</span>
            </div>
        @endforeach
    </div>
</div>
