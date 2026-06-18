@props([
    'title' => null,
    'description' => null,
    'padding' => 'default',
])

@php
    $paddingClass = [
        'compact' => 'p-4 sm:p-5',
        'default' => 'p-5 sm:p-6 lg:p-7',
    ][$padding];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-line bg-surface shadow-panel '.$paddingClass]) }}>
    @if ($title || $description)
        <div class="mb-4">
            @if ($title)
                <h3 class="text-lg font-semibold leading-7 text-ink">{{ $title }}</h3>
            @endif

            @if ($description)
                <p class="mt-1 text-sm leading-6 text-muted">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
