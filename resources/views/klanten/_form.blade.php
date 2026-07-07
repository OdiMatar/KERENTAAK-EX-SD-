@php
    $klant = $klant ?? null;
    $method = $method ?? 'POST';
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="row g-3"
    data-klant-form
    data-existing-customers='@json($bestaandeKlanten ?? [])'
    novalidate
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="col-12">
        <div
            class="alert alert-danger mb-0 {{ $errors->any() ? '' : 'd-none' }}"
            role="alert"
            data-klant-form-alert
        >
            De gegevens van deze klant zijn niet bijgewerkt.
        </div>
    </div>

    <div class="col-md-6">
        <x-ui.input
            label="Naam"
            name="naam"
            value="{{ old('naam', $klant?->naam ?? '') }}"
            :error="$errors->first('naam')"
            data-klant-error="naam"
            required
        />
    </div>

    <div class="col-md-6">
        <x-ui.input
            label="Telefoonnummer"
            name="telefoonnummer"
            value="{{ old('telefoonnummer', $klant?->telefoonnummer ?? '') }}"
            :error="$errors->first('telefoonnummer')"
            data-klant-error="telefoonnummer"
            required
        />
    </div>

    <div class="col-md-6">
        <x-ui.input
            label="Adres"
            name="adres"
            value="{{ old('adres', $klant?->adres ?? '') }}"
            placeholder="Teststraat 1, 1234 AB Utrecht"
            hint="Vul straatnaam, huisnummer, postcode en stad in."
            :error="$errors->first('adres')"
            data-klant-error="adres"
            required
        />
    </div>

    <div class="col-md-6">
        <x-ui.input
            label="E-mailadres"
            name="email"
            value="{{ old('email', $klant?->email ?? '') }}"
            :error="$errors->first('email')"
            data-klant-error="email"
            required
        />
    </div>

    <div class="col-md-6">
        <label for="is_actief" class="form-label fw-medium">Status</label>
        <select id="is_actief" name="is_actief" class="form-select" required>
            <option value="1" @selected((string) old('is_actief', (int) ($klant?->is_actief ?? true)) === '1')>Actief</option>
            <option value="0" @selected((string) old('is_actief', (int) ($klant?->is_actief ?? true)) === '0')>Inactief</option>
        </select>
        @if ($errors->first('is_actief'))
            <div class="form-text text-danger">{{ $errors->first('is_actief') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="wensen" class="form-label fw-medium">Specifieke wensen</label>
        <textarea id="wensen" name="wensen" class="form-control" rows="3" maxlength="255">{{ old('wensen', $wensen ?? '') }}</textarea>
        @if ($errors->first('wensen'))
            <div class="form-text text-danger">{{ $errors->first('wensen') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="allergieen" class="form-label fw-medium">Allergieën</label>
        <textarea id="allergieen" name="allergieen" class="form-control" rows="3" maxlength="255">{{ old('allergieen', $allergieen ?? '') }}</textarea>
        @if ($errors->first('allergieen'))
            <div class="form-text text-danger">{{ $errors->first('allergieen') }}</div>
        @endif
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('klanten.index') }}" class="btn btn-outline-secondary">Annuleren</a>
    </div>
</form>
