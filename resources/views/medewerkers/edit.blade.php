<x-app-layout title="Medewerker wijzigen">
    <x-ui.container class="py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-semibold small text-muted mb-2">Medewerkers</p>
                <h1 class="h2 mb-0">Medewerker wijzigen</h1>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('medewerkers.update', $medewerker) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">Naam</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $medewerker->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $medewerker->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Functie</label>
                        <select name="role" class="form-select" required>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $medewerker->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefoon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $medewerker->phone) }}">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Opslaan</button>
                        <a href="{{ route('medewerkers.index') }}" class="btn btn-outline-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </x-ui.container>
</x-app-layout>
