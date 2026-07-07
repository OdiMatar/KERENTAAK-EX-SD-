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
            ->where('is_active', true)
            ->when(
                $selectedRole !== '' && array_key_exists($selectedRole, $roles),
                fn ($query) => $query->where('role', $selectedRole)
            )
            ->orderBy('name', 'asc')
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

        $medewerker = Medewerker::create($request->validated());

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
        $data = $request->validated();

        $hasChanges = collect($data)->contains(
            fn ($value, $field) => (string) ($medewerker->{$field} ?? '') !== (string) ($value ?? '')
        );

        if (! $hasChanges) {
            return redirect()
                ->route('medewerkers.index')
                ->with('status', 'Er zijn geen medewerkergegevens gewijzigd');
        }

        $medewerker->update($data);

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
}
