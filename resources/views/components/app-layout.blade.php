@props([
    'title' => config('app.name', 'Laravel'),
    'navVariant' => 'default',
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
    <body class="min-vh-100 bg-soft">
        <div class="min-vh-100 d-flex flex-column">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible auto-dismiss rounded-0 border-0 mb-0" role="alert">
                    <x-ui.container>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <span>{{ session('status') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
                        </div>
                    </x-ui.container>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible auto-dismiss rounded-0 border-0 mb-0" role="alert">
                    <x-ui.container>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <span>{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
                        </div>
                    </x-ui.container>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible auto-dismiss rounded-0 border-0 mb-0" role="alert">
                    <x-ui.container>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <span>Controleer de ingevulde gegevens.</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
                        </div>
                    </x-ui.container>
                </div>
            @endif

            <x-site-navbar :variant="$navVariant" />

            <div class="flex-grow-1">
                {{ $slot }}
            </div>

            <x-site-footer />
        </div>
    </body>
</html>
