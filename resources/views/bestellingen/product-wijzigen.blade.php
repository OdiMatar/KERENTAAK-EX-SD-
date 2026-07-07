<x-app-layout title="Product wijzigen">
    <main>
        <x-ui.section
            eyebrow="Bestellingen"
            title="Product wijzigen"
            description="Pas de productgegevens aan voor deze bestelling."
        >
            <x-ui.card>
                <form method="POST" action="{{ route('bestellingen.producten.update', [$bestelling->id, $product->id]) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <x-ui.input label="Productnaam" name="naam" value="{{ old('naam', $product->naam) }}" :error="$errors->first('naam')" />
                    </div>

                    <div class="col-md-6">
                        <label for="categorie" class="form-label fw-medium">Categorie</label>
                        <select id="categorie" name="categorie" class="form-select">
                            @foreach (['shampoo', 'conditioner', 'styling', 'verf', 'overig'] as $categorie)
                                <option value="{{ $categorie }}" @selected(old('categorie', $product->categorie) === $categorie)>{{ ucfirst($categorie) }}</option>
                            @endforeach
                        </select>
                        @error('categorie')
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="ean_code_weergave" class="form-label fw-medium">EAN-code</label>
                        <input
                            id="ean_code_weergave"
                            type="text"
                            class="form-control"
                            value="{{ $product->ean_code }}"
                            disabled
                            readonly
                            aria-readonly="true"
                            aria-disabled="true"
                            tabindex="-1"
                        >
                    </div>

                    <div class="col-md-4">
                        <x-ui.input label="Prijs" name="prijs" type="number" step="0.01" min="0" value="{{ old('prijs', $product->prijs) }}" :error="$errors->first('prijs')" />
                    </div>

                    <div class="col-md-4">
                        <x-ui.input label="Voorraad" name="voorraad" type="number" min="0" value="{{ old('voorraad', $product->voorraad) }}" :error="$errors->first('voorraad')" />
                    </div>

                    <div class="col-md-6">
                        <x-ui.input label="Leverancier" name="leverancier" value="{{ old('leverancier', $product->leverancier) }}" :error="$errors->first('leverancier')" />
                    </div>

                    <div class="col-md-6">
                        <input type="hidden" name="is_actief" value="0">
                        <div class="form-check mt-md-4 pt-md-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_actief"
                                id="is_actief"
                                value="1"
                                @checked((bool) old('is_actief', $product->is_actief))
                            >
                            <label class="form-check-label" for="is_actief">Product is actief</label>
                        </div>
                        @error('is_actief')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <x-ui.button type="submit">Wijzigen</x-ui.button>
                        <x-ui.button variant="secondary" href="{{ route('bestellingen.show', $bestelling->id) }}">Annuleren</x-ui.button>
                    </div>
                </form>

                <form
                    method="POST"
                    action="{{ route('bestellingen.producten.destroy', [$bestelling->id, $product->id]) }}"
                    class="mt-3"
                    onsubmit="return confirm('Weet je zeker dat je dit product uit de voorraad wilt verwijderen?')"
                >
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Verwijder uit voorraad</x-ui.button>
                </form>
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
