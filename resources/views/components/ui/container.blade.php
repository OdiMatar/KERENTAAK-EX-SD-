@props([
    'size' => 'default',
])

@php
    $sizes = [
        'narrow' => 'container-sm',
        'default' => 'container',
        'wide' => 'container-fluid px-4 px-lg-5',
    ];
@endphp

<div {{ $attributes->merge(['class' => $sizes[$size]]) }}>
    {{ $slot }}
</div>
