<x-app-layout title="Bestelling details">
    <main>
        <x-ui.section
            eyebrow="Bestellingen"
            title="Bestelling van {{ $bestelling->klant_naam }}"
            description="Bestelde producten, aantallen en prijzen van deze bestelling."
        >
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <x-ui.card title="Besteldatum" description="{{ $bestelling->orderdatum->format('d-m-Y') }}" />
                </div>
                <div class="col-md-3">
                    <x-ui.card title="Leverdatum" description="{{ $bestelling->verwachte_leverdatum->format('d-m-Y') }}" />
                </div>
                <div class="col-md-3">
                    <x-ui.card title="Status" description="{{ $bestelling->status }}" />
                </div>
                <div class="col-md-3">
                    <x-ui.card title="Totaalprijs" description="€ {{ number_format((float) $bestelling->totaalprijs, 2, ',', '.') }}" />
                </div>
            </div>

            <x-ui.card>
                <div class="d-flex justify-content-end mb-3">
                    <x-ui.button href="{{ route('bestellingen.producten.create', $bestelling->id) }}">Nieuwe product aanmaken</x-ui.button>
                </div>

                <form method="POST" action="{{ route('bestellingen.regels.store', $bestelling->id) }}" class="row g-3 mb-4">
                    @csrf

                    <div class="col-md-8">
                        <label for="product_id" class="form-label fw-medium">Product toevoegen</label>
                        <select id="product_id" name="product_id" class="form-select">
                            <option value="">Kies een product</option>
                            @foreach ($producten as $product)
                                <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>
                                    {{ $product->naam }} - € {{ number_format((float) $product->prijs, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <x-ui.input label="Aantal" name="aantal" type="number" min="1" value="{{ old('aantal', 1) }}" :error="$errors->first('aantal')" />
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <x-ui.button type="submit" class="w-100">Toevoegen</x-ui.button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="small text-uppercase text-muted">
                            <tr>
                                <th>Product</th>
                                <th>Aantal</th>
                                <th>Prijs per stuk</th>
                                <th>Subtotaal</th>
                                <th>Categorie</th>
                                <th>EAN-code</th>
                                <th>Leverancier</th>
                                <th>Voorraad</th>
                                <th class="text-end">Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bestelregels as $regel)
                                <tr>
                                    <td class="fw-semibold">{{ $regel->product_naam }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('bestellingen.regels.update', [$bestelling->id, $regel->id]) }}" class="d-flex gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input
                                                type="number"
                                                name="aantal"
                                                min="1"
                                                value="{{ $regel->aantal }}"
                                                class="form-control form-control-sm"
                                                style="max-width: 6rem;"
                                                aria-label="Aantal"
                                            >
                                            <x-ui.button type="submit" size="sm" variant="secondary">Opslaan</x-ui.button>
                                        </form>
                                    </td>
                                    <td>€ {{ number_format((float) $regel->prijs_per_stuk, 2, ',', '.') }}</td>
                                    <td>€ {{ number_format((float) $regel->subtotaal, 2, ',', '.') }}</td>
                                    <td>{{ ucfirst($regel->categorie) }}</td>
                                    <td>{{ $regel->ean_code }}</td>
                                    <td>{{ $regel->leverancier }}</td>
                                    <td>{{ $regel->voorraad }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <x-ui.button size="sm" href="{{ route('bestellingen.producten.edit', [$bestelling->id, $regel->product_id]) }}">Product wijzigen</x-ui.button>

                                            <form method="POST" action="{{ route('bestellingen.regels.destroy', [$bestelling->id, $regel->id]) }}" onsubmit="return confirm('Weet je zeker dat je dit product uit deze bestelling wilt verwijderen?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="danger" size="sm">Uit bestelling</x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-4 text-muted">Er zijn geen producten gekoppeld aan deze bestelling.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <div class="mt-3">
                <x-ui.button variant="secondary" href="{{ route('bestellingen.index') }}">Terug naar bestellingen</x-ui.button>
            </div>
        </x-ui.section>
    </main>
</x-app-layout>
