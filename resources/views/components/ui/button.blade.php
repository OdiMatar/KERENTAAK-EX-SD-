@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'app-focus inline-flex min-h-11 items-center justify-center gap-2 rounded-control border px-4 font-semibold transition';
    $variants = [
        'primary' => 'border-brand-600 bg-brand-600 text-white hover:bg-brand-700',
        'secondary' => 'border-line bg-surface text-ink hover:bg-slate-50',
        'ghost' => 'border-transparent bg-transparent text-ink hover:bg-slate-100',
        'danger' => 'border-red-600 bg-red-600 text-white hover:bg-red-700',
    ];
    $sizes = [
        'sm' => 'min-h-10 px-3 text-sm',
        'md' => 'min-h-11 px-4 text-sm',
        'lg' => 'min-h-12 px-5 text-base',
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
