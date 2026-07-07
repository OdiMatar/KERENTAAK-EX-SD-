<x-app-layout title="Bestellingen">
    <main>
        <x-ui.section
            eyebrow="Bestellingen"
            title="Bestellingen overzicht"
            description="Besteldatum, verwachte leverdatum en status van klantbestellingen."
        >
            <div class="d-flex justify-content-end mb-3">
                <x-ui.button href="{{ route('bestellingen.create') }}">Bestelling toevoegen</x-ui.button>
            </div>

            <x-ui.card>
                @if ($bestellingen->isEmpty())
                    <p class="mb-0 text-muted">Er zijn geen bestellingen beschikbaar.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="small text-uppercase text-muted">
                                <tr>
                                    <th>Klant</th>
                                    <th>Besteldatum</th>
                                    <th>Verwachte leverdatum</th>
                                    <th>Status</th>
                                    <th>Totaalprijs</th>
                                    <th class="text-end">Acties</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bestellingen as $bestelling)
                                    <tr>
                                        <td class="fw-semibold">
                                            <a href="{{ route('bestellingen.show', $bestelling->id) }}" class="text-brand">
                                                {{ $bestelling->klant_naam }}
                                            </a>
                                        </td>
                                        <td>{{ $bestelling->orderdatum->format('d-m-Y') }}</td>
                                        <td>{{ $bestelling->verwachte_leverdatum->format('d-m-Y') }}</td>
                                        <td>{{ $bestelling->status }}</td>
                                        <td>€ {{ number_format((float) $bestelling->totaalprijs, 2, ',', '.') }}</td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <x-ui.button size="sm" variant="secondary" href="{{ route('bestellingen.show', $bestelling->id) }}">Details</x-ui.button>
                                                <form
                                                    method="POST"
                                                    action="{{ route('bestellingen.destroy', $bestelling->id) }}"
                                                    onsubmit="return confirm('Weet je zeker dat je deze bestelling wilt verwijderen?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button size="sm" variant="danger" type="submit">Verwijderen</x-ui.button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($bestellingen->hasPages())
                        <nav class="mt-3" aria-label="Paginering bestellingen">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item @if ($bestellingen->onFirstPage()) disabled @endif">
                                    <a
                                        class="page-link"
                                        href="{{ $bestellingen->onFirstPage() ? '#' : $bestellingen->previousPageUrl() }}"
                                        aria-label="Vorige pagina"
                                    >&lsaquo;</a>
                                </li>

                                @for ($pagina = 1; $pagina <= $bestellingen->lastPage(); $pagina++)
                                    <li class="page-item @if ($pagina === $bestellingen->currentPage()) active @endif">
                                        <a class="page-link" href="{{ $bestellingen->url($pagina) }}">{{ $pagina }}</a>
                                    </li>
                                @endfor

                                <li class="page-item @if (! $bestellingen->hasMorePages()) disabled @endif">
                                    <a
                                        class="page-link"
                                        href="{{ $bestellingen->hasMorePages() ? $bestellingen->nextPageUrl() : '#' }}"
                                        aria-label="Volgende pagina"
                                    >&rsaquo;</a>
                                </li>
                            </ul>
                        </nav>
                    @endif
                @endif
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
