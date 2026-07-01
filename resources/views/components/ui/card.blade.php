@props([
    'title' => null,
    'description' => null,
    'padding' => 'default',
])

@php
    $paddingClass = [
        'compact' => 'p-3',
        'default' => 'p-4',
    ][$padding];
@endphp

<div {{ $attributes->merge(['class' => 'card site-card h-100 '.$paddingClass]) }}>
    @if ($title || $description)
        <div class="mb-3">
            @if ($title)
                <h3 class="h5 fw-semibold mb-1">{{ $title }}</h3>
            @endif

            @if ($description)
                <p class="small text-muted-custom mb-0">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
