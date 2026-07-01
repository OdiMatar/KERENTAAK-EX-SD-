@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'py-5']) }}>
    <x-ui.container>
        @if ($eyebrow || $title || $description)
            <div class="mb-4 col-lg-8">
                @if ($eyebrow)
                    <p class="mb-2 small fw-semibold text-brand">{{ $eyebrow }}</p>
                @endif

                @if ($title)
                    <h2 class="h3 fw-semibold">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="mt-3 text-muted-custom">{{ $description }}</p>
                @endif
            </div>
        @endif

        {{ $slot }}
    </x-ui.container>
</section>
