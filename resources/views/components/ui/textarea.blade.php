@props([
    'label' => null,
    'name',
    'rows' => 4,
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

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->except('id')->merge([
            'class' => 'form-control',
        ]) }}
    >{{ $slot }}</textarea>

    @if ($error)
        <div class="form-text text-danger">{{ $error }}</div>
    @elseif ($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif
</div>
