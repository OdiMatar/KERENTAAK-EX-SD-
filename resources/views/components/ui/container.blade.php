@props([
    'size' => 'default',
])

@php
    $sizes = [
        'narrow' => 'max-w-3xl',
        'default' => 'max-w-6xl',
        'wide' => 'max-w-7xl',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'mx-auto w-full px-4 sm:px-6 lg:px-8 '.$sizes[$size]]) }}>
    {{ $slot }}
</div>
