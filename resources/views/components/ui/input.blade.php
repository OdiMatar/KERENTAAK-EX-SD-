@props([
    'label' => null,
    'name',
    'type' => 'text',
    'hint' => null,
    'error' => null,
])

@php
    $id = $attributes->get('id', $name);
@endphp

<label for="{{ $id }}" class="block">
    @if ($label)
        <span class="mb-2 block text-sm font-medium text-ink">{{ $label }}</span>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->except('id')->merge([
            'class' => 'app-focus block min-h-11 w-full rounded-control border border-line bg-surface px-3 text-base text-ink shadow-sm placeholder:text-slate-400 sm:text-sm',
        ]) }}
    >

    @if ($error)
        <span class="mt-2 block text-sm text-red-600">{{ $error }}</span>
    @elseif ($hint)
        <span class="mt-2 block text-sm text-muted">{{ $hint }}</span>
    @endif
</label>
