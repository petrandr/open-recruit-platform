<div class="p-4 py-4">
    <h5>{{ __('Available Placeholders') }}</h5>
    <p class="text-muted">{{ __('You can use the following placeholders in your template body:') }}</p>
    <div class="d-flex flex-wrap gap-2">
        @foreach(config('platform.notification_templates.placeholders', []) as $key => $label)
            <span class="badge bg-secondary placeholder-badge" style="cursor: pointer;"
                  title="{{ $label }}"
                  data-placeholder="{!! '&#123;&#123;'.$key.'&#125;&#125;' !!}"
                  onclick="appendPlaceholderToTextarea(this.dataset.placeholder)">
                <code class="text-white">{!! '&#123;&#123;'.$key.'&#125;&#125;' !!}</code>
            </span>
        @endforeach
    </div>
</div>
<script src="{{ asset('js/custom.js') }}"></script>
