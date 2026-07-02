<x-app-layout title="Overzicht medewerkers">
    <x-ui.container class="py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-semibold small text-muted mb-2">Medewerkers</p>
                <h1 class="h2 mb-0">Overzicht medewerkers</h1>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                    <form method="GET" action="{{ route('medewerkers.index') }}" class="d-flex align-items-center gap-2">
                        <label class="mb-0 fw-semibold">Filter op rol</label>
                        <select name="role" class="form-select" onchange="this.form.submit()">
                            <option value="" {{ $selectedRole === '' ? 'selected' : '' }}>Alle rollen</option>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ $selectedRole === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>

                    <a href="{{ route('medewerkers.create') }}" class="btn btn-primary">Medewerker toevoegen</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Naam</th>
                                <th>Functie</th>
                                <th>E-mail</th>
                                <th>Telefoon</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($medewerkers as $medewerker)
                                <tr>
                                    <td>{{ $medewerker->name }}</td>
                                    <td>{{ $medewerker->role }}</td>
                                    <td>{{ $medewerker->email }}</td>
                                    <td>{{ $medewerker->phone ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('medewerkers.edit', $medewerker) }}" class="btn btn-sm btn-outline-secondary">Wijzigen</a>
                                            <form method="POST" action="{{ route('medewerkers.destroy', $medewerker) }}" onsubmit="return confirm('Weet je zeker dat je deze medewerker wilt verwijderen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">
                                        @if ($selectedRole === App\Models\Medewerker::ROLE_VOLUNTEER)
                                            Er zijn momenteel geen vrijwilligers bekend.
                                        @elseif ($selectedRole === App\Models\Medewerker::ROLE_INTERN)
                                            Er zijn momenteel geen stagairs bekend.
                                        @else
                                            Er zijn momenteel geen medewerkers bekend.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-ui.container>
</x-app-layout>
