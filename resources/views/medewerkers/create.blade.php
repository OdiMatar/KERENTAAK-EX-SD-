<x-app-layout title="Medewerker toevoegen">
    <x-ui.container class="py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-semibold small text-muted mb-2">Medewerkers</p>
                <h1 class="h2 mb-0">Medewerker toevoegen</h1>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('medewerkers.store') }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <x-ui.input
                            label="Naam"
                            name="name"
                            value="{{ old('name') }}"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>
                    <div class="col-md-6">
                        <x-ui.input
                            label="E-mail"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            :error="$errors->first('email')"
                            required
                        />
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label">Functie</label>
                        <select id="role" name="role" class="form-select" required>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', App\Models\Medewerker::ROLE_EMPLOYEE) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if ($errors->first('role'))
                            <div class="form-text text-danger">{{ $errors->first('role') }}</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <x-ui.input
                            label="Telefoon"
                            name="phone"
                            value="{{ old('phone') }}"
                            :error="$errors->first('phone')"
                        />
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Toevoegen</button>
                        <a href="{{ route('medewerkers.index') }}" class="btn btn-outline-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </x-ui.container>
</x-app-layout>
