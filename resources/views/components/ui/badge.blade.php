@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'text-bg-secondary',
        'brand' => 'text-bg-success',
        'success' => 'text-bg-success',
        'warning' => 'text-bg-warning',
        'danger' => 'text-bg-danger',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill '.$variants[$variant]]) }}>
    {{ $slot }}
</span>
