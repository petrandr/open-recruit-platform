<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Application Rejection') }}</title>
</head>
<body>
    {{-- Render full rejection message from textarea, preserving line breaks --}}
    <div>{!! nl2br(e($message_text)) !!}</div>
</body>
</html>