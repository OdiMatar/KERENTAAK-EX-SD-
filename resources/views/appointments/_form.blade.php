@php
    $method = $method ?? 'POST';
    $appointment = $appointment ?? null;
    $selectedTreatmentId = $selectedTreatmentId ?? 0;
    $currentTreatmentId = old('behandeling_id', $appointment->behandeling_id ?? $selectedTreatmentId);
    $currentEmployeeId = old('medewerker_id', $appointment->medewerker_id ?? '');
    $currentDate = old('datum', $appointment->datum ?? '');
    $currentStartTime = old('starttijd', $appointment->starttijd ?? '');
@endphp

<form method="POST" action="{{ $action }}" class="appointment-form" data-appointment-form novalidate>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="appointment-steps" aria-label="Stappen afspraak maken">
        @foreach (['Behandeling', 'Medewerker', 'Datum', 'Tijd'] as $step => $label)
            <div class="appointment-step {{ $step === 0 ? 'is-active' : '' }}">
                <span>{{ $step + 1 }}</span>
                <small>{{ $label }}</small>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mt-2">
        <div class="col-12">
            <h2 class="h4 fw-semibold mb-0">Kies een behandeling</h2>
        </div>

        <div class="col-12">
            <div class="appointment-options">
                @foreach ($treatments as $treatment)
                    <label class="appointment-option">
                        <input
                            class="btn-check"
                            type="radio"
                            name="behandeling_id"
                            value="{{ $treatment->id }}"
                            required
                            @checked((int) $currentTreatmentId === $treatment->id)
                        >
                        <span>
                            <strong>{{ $treatment->naam }}</strong>
                            <small>{{ $treatment->duur }} min - vanaf &euro;{{ number_format((float) $treatment->prijs, 0, ',', '.') }}</small>
                        </span>
                        <span class="btn btn-brand fw-semibold">Kies</span>
                    </label>
                @endforeach
            </div>

            @error('behandeling_id')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>

        <div class="col-lg-4">
            <label for="medewerker_id" class="form-label fw-semibold">Medewerker</label>
            <select id="medewerker_id" name="medewerker_id" class="form-select" required>
                <option value="">Kies een medewerker</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected((int) $currentEmployeeId === $employee->id)>
                        {{ $employee->volledigeNaam() }}
                    </option>
                @endforeach
            </select>
            @error('medewerker_id')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>

        <div class="col-sm-6 col-lg-4">
            <label for="datum" class="form-label fw-semibold">Datum</label>
            <input id="datum" name="datum" type="date" value="{{ $currentDate }}" min="{{ now()->toDateString() }}" class="form-control" required>
            @error('datum')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>

        <div class="col-sm-6 col-lg-4">
            <label for="starttijd" class="form-label fw-semibold">Starttijd</label>
            <select id="starttijd" name="starttijd" class="form-select" required>
                <option value="">Kies een tijd</option>

                @for ($hour = 9; $hour < 19; $hour++)
                    @foreach ([0, 15, 30, 45] as $minute)
                        @php
                            $timeValue = sprintf('%02d:%02d', $hour, $minute);
                        @endphp

                        <option value="{{ $timeValue }}" @selected($currentStartTime === $timeValue)>
                            {{ $timeValue }}
                        </option>
                    @endforeach
                @endfor

                <option value="19:00" disabled>19:00 gesloten</option>
            </select>
            @error('starttijd')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between mt-4">
        <x-ui.button variant="secondary" href="{{ route('home') }}">Terug Naar Home</x-ui.button>
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
