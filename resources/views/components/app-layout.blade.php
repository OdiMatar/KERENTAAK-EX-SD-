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
            @if (session('status'))
                <div class="border-b border-emerald-200 bg-emerald-50">
                    <x-ui.container>
                        <p class="py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                    </x-ui.container>
                </div>
            @endif

            {{ $slot }}
        </div>
    </body>
</html>
