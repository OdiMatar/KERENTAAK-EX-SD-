<x-app-layout title="Nieuwe product aanmaken">
    <main>
        <x-ui.section
            eyebrow="Bestellingen"
            title="Nieuwe product aanmaken"
            description="Voeg een product toe aan de voorraad."
        >
            <x-ui.card>
                <form method="POST" action="{{ route('bestellingen.producten.store', $bestelling->id) }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <x-ui.input label="Productnaam" name="naam" value="{{ old('naam') }}" :error="$errors->first('naam')" />
                    </div>

                    <div class="col-md-6">
                        <label for="categorie" class="form-label fw-medium">Categorie</label>
                        <select id="categorie" name="categorie" class="form-select">
                            <option value="">Kies een categorie</option>
                            @foreach ($categorieen as $categorie)
                                <option value="{{ $categorie }}" @selected(old('categorie') === $categorie)>{{ ucfirst($categorie) }}</option>
                            @endforeach
                        </select>
                        @error('categorie')
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <x-ui.input label="EAN-code" name="ean_code" value="{{ old('ean_code') }}" :error="$errors->first('ean_code')" />
                    </div>

                    <div class="col-md-4">
                        <x-ui.input label="Prijs" name="prijs" type="number" step="0.01" min="0" value="{{ old('prijs') }}" :error="$errors->first('prijs')" />
                    </div>

                    <div class="col-md-4">
                        <x-ui.input label="Voorraad" name="voorraad" type="number" min="0" value="{{ old('voorraad') }}" :error="$errors->first('voorraad')" />
                    </div>

                    <div class="col-12">
                        <x-ui.input label="Leverancier" name="leverancier" value="{{ old('leverancier') }}" :error="$errors->first('leverancier')" />
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <x-ui.button type="submit">Toevoegen</x-ui.button>
                        <x-ui.button variant="secondary" href="{{ route('bestellingen.show', $bestelling->id) }}">Annuleren</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
