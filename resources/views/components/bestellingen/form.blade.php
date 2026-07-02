@props([
    'bestelling' => null,
    'action',
    'method' => 'POST',
    'buttonText',
    'klanten' => collect(),
    'producten' => collect(),
])

<form method="POST" action="{{ $action }}" class="row g-3">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($method === 'POST')
        <div class="col-md-6">
            <label for="klant_naam" class="form-label fw-medium">Klant</label>
            <select id="klant_naam" name="klant_naam" class="form-select">
                <option value="">Kies een klant</option>
                @foreach ($klanten as $klant)
                    <option value="{{ $klant }}" @selected(old('klant_naam', $bestelling->klant_naam ?? '') === $klant)>{{ $klant }}</option>
                @endforeach
            </select>
            @error('klant_naam')
                <div class="form-text text-danger">{{ $message }}</div>
            @enderror
        </div>
    @else
        <div class="col-md-6">
            <x-ui.input label="Klantnaam" name="klant_naam" value="{{ old('klant_naam', $bestelling->klant_naam ?? '') }}" :error="$errors->first('klant_naam')" />
        </div>
    @endif

    <div class="col-md-3">
        <x-ui.input label="Besteldatum" name="orderdatum" type="date" value="{{ old('orderdatum', isset($bestelling->orderdatum) ? $bestelling->orderdatum->format('Y-m-d') : now()->format('Y-m-d')) }}" :error="$errors->first('orderdatum')" />
    </div>

    <div class="col-md-3">
        <x-ui.input label="Verwachte leverdatum" name="verwachte_leverdatum" type="date" value="{{ old('verwachte_leverdatum', isset($bestelling->verwachte_leverdatum) ? $bestelling->verwachte_leverdatum->format('Y-m-d') : now()->addDays(3)->format('Y-m-d')) }}" :error="$errors->first('verwachte_leverdatum')" />
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label fw-medium">Status</label>
        <select id="status" name="status" class="form-select">
            @foreach (['Nieuw', 'Verwerkt', 'Afgerond', 'Geannuleerd'] as $status)
                <option value="{{ $status }}" @selected(old('status', $bestelling->status ?? 'Nieuw') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="form-text text-danger">{{ $message }}</div>
        @enderror
    </div>

    @if ($method === 'POST')
        <div class="col-md-6">
            <label for="product_id" class="form-label fw-medium">Product</label>
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

        <div class="col-md-6">
            <x-ui.input label="Aantal" name="aantal" type="number" min="1" value="{{ old('aantal', 1) }}" :error="$errors->first('aantal')" />
        </div>
    @endif

    <div class="col-12">
        <x-ui.textarea label="Opmerking" name="opmerking" :error="$errors->first('opmerking')">{{ old('opmerking', $bestelling->opmerking ?? '') }}</x-ui.textarea>
    </div>

    <div class="col-12 d-flex gap-2">
        <x-ui.button type="submit">{{ $buttonText }}</x-ui.button>
        <x-ui.button variant="secondary" href="{{ route('bestellingen.index') }}">Annuleren</x-ui.button>
    </div>
</form>
