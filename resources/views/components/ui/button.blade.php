@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'btn fw-semibold';
    $variants = [
        'primary' => 'btn-brand',
        'secondary' => 'btn-outline-secondary bg-white',
        'overlay-secondary' => 'btn-overlay',
        'ghost' => 'btn-light',
        'danger' => 'btn-danger',
    ];
    $sizes = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];
    $class = $base.' '.$variants[$variant].' '.$sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </button>
@endif
