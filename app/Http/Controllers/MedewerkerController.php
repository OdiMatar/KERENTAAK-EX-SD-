<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedewerkerRequest;
use App\Http\Requests\UpdateMedewerkerRequest;
use App\Models\Medewerker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedewerkerController extends Controller
{
    private function ensureNotCustomer(Request $request): void
    {
        if ($request->user()?->isCustomer()) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->ensureNotCustomer($request);
        $roles = Medewerker::roles();
        $selectedRole = $request->query('role', '');

        $medewerkers = Medewerker::query()
            ->where('is_actief', true)
            ->when(
                $selectedRole !== '' && array_key_exists($selectedRole, $roles),
                fn ($query) => $query->where('functie', $roles[$selectedRole])
            )
            ->orderBy('voornaam', 'asc')
            ->orderBy('achternaam', 'asc')
            ->get();

        return view('medewerkers.index', compact('medewerkers', 'roles', 'selectedRole'));
    }

    public function create(Request $request): View
    {
        $this->ensureNotCustomer($request);

        return view('medewerkers.create', [
            'roles' => Medewerker::roles(),
        ]);
    }

    public function store(StoreMedewerkerRequest $request): RedirectResponse
    {
        $this->ensureNotCustomer($request);

        $medewerker = Medewerker::create($this->medewerkerData($request->validated()));

        return redirect()
            ->route('medewerkers.index')
            ->with('status', 'De medewerker is succesvol toegevoegd.')
            ->with('highlighted_medewerker_id', $medewerker->id);
    }

    public function edit(Request $request, Medewerker $medewerker): View
    {
        $this->ensureNotCustomer($request);

        return view('medewerkers.edit', [
            'medewerker' => $medewerker,
            'roles' => Medewerker::roles(),
        ]);
    }

    public function update(UpdateMedewerkerRequest $request, Medewerker $medewerker): RedirectResponse
    {
        $this->ensureNotCustomer($request);
        $medewerker->update($this->medewerkerData($request->validated()));

        return redirect()
            ->route('medewerkers.index')
            ->with('status', 'De medewerker is succesvol gewijzigd.')
            ->with('highlighted_medewerker_id', $medewerker->id);
    }

    public function destroy(Request $request, Medewerker $medewerker): RedirectResponse
    {
        $this->ensureNotCustomer($request);

        if ($medewerker->afspraken()->exists()) {
            $medewerker->update([
                'is_active' => false,
                'is_actief' => false,
            ]);

            return redirect()->route('medewerkers.index')->with('status', 'De medewerker is succesvol verwijderd.');
        }

        $medewerker->delete();

        return redirect()->route('medewerkers.index')->with('status', 'De medewerker is succesvol verwijderd.');
    }

    /**
     * @param  array{name: string, email: string, role: string, phone?: string|null}  $data
     * @return array{name: string, voornaam: string, achternaam: string, email: string, role: string, functie: string, phone: string|null, telefoonnummer: string|null, is_actief: bool}
     */
    private function medewerkerData(array $data): array
    {
        $name = trim($data['name']);
        $nameParts = preg_split('/\s+/', $name, 2) ?: [$name];
        $roles = Medewerker::roles();

        return [
            'name' => $name,
            'voornaam' => $nameParts[0],
            'achternaam' => $nameParts[1] ?? '-',
            'email' => $data['email'],
            'role' => $data['role'],
            'functie' => $roles[$data['role']] ?? $roles[Medewerker::ROLE_EMPLOYEE],
            'phone' => $data['phone'] ?? null,
            'telefoonnummer' => $data['phone'] ?? null,
            'is_actief' => true,
        ];
    }
}
