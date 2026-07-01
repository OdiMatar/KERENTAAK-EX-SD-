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

<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label fw-medium">{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->except('id')->merge([
            'class' => 'form-control',
        ]) }}
    >

    @if ($error)
        <div class="form-text text-danger">{{ $error }}</div>
    @elseif ($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif
</div>
