<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
    </style>
</head>
<body>
    {!! $body !!}
</body>
</html>
