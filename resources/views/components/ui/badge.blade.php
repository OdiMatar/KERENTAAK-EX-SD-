@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-100',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-100',
        'danger' => 'bg-red-50 text-red-700 ring-red-100',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$variants[$variant]]) }}>
    {{ $slot }}
</span>
