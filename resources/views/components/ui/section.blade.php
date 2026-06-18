@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'py-8 sm:py-10 lg:py-14']) }}>
    <x-ui.container>
        @if ($eyebrow || $title || $description)
            <div class="mb-6 max-w-3xl sm:mb-8">
                @if ($eyebrow)
                    <p class="mb-2 text-sm font-semibold text-brand-600">{{ $eyebrow }}</p>
                @endif

                @if ($title)
                    <h2 class="text-2xl font-semibold leading-tight text-ink sm:text-3xl">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="mt-3 text-base leading-7 text-muted">{{ $description }}</p>
                @endif
            </div>
        @endif

        {{ $slot }}
    </x-ui.container>
</section>
