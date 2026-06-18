@props([
    'title' => config('app.name', 'Laravel'),
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-soft font-sans antialiased text-ink">
        <div class="min-h-screen">
            {{ $slot }}
        </div>
    </body>
</html>
