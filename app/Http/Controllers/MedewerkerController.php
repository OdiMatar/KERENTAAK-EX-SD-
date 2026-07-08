<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedewerkerRequest;
use App\Http\Requests\UpdateMedewerkerRequest;
use App\Models\Medewerker;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

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
        $selectedRole = is_string($selectedRole) ? $selectedRole : '';
        $medewerkers = Medewerker::voorOverzicht($selectedRole);

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

        try {
            $medewerker = Medewerker::create($this->medewerkerData($request->validated()));

            return redirect()
                ->route('medewerkers.index')
                ->with('status', 'De medewerker is succesvol toegevoegd.')
                ->with('highlighted_medewerker_id', $medewerker->id);
        } catch (QueryException $exception) {
            return $this->terugMetDatabaseFout($exception, 'Medewerker toevoegen is niet gelukt.');
        } catch (Throwable $exception) {
            Log::error('Medewerker toevoegen mislukt.', ['exception' => $exception]);

            return back()
                ->withInput()
                ->with('error', 'Medewerker toevoegen is niet gelukt.');
        }
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
        try {
            $data = $this->medewerkerData($request->validated());

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
        } catch (QueryException $exception) {
            return $this->terugMetDatabaseFout($exception, 'Medewerker wijzigen is niet gelukt.');
        } catch (Throwable $exception) {
            Log::error('Medewerker wijzigen mislukt.', [
                'medewerker_id' => $medewerker->id,
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Medewerker wijzigen is niet gelukt.');
        }
    }

    public function destroy(Request $request, Medewerker $medewerker): RedirectResponse
    {
        $this->ensureNotCustomer($request);

        try {
            if ($medewerker->is_active !== null && ! (bool) $medewerker->is_active) {
                return redirect()->route('medewerkers.index')->with('status', 'Deze medewerker is al verwijderd');
            }

            $medewerker->update([
                'is_active' => false,
                'is_actief' => false,
            ]);

            return redirect()->route('medewerkers.index')->with('status', 'De medewerker is succesvol verwijderd.');
        } catch (QueryException $exception) {
            return back()->with('error', $this->databaseFoutmelding($exception) ?? 'Medewerker verwijderen is niet gelukt.');
        } catch (Throwable $exception) {
            Log::error('Medewerker verwijderen mislukt.', [
                'medewerker_id' => $medewerker->id,
                'exception' => $exception,
            ]);

            return back()->with('error', 'Medewerker verwijderen is niet gelukt.');
        }
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

    private function terugMetDatabaseFout(QueryException $exception, string $fallbackMessage): RedirectResponse
    {
        return back()
            ->withInput()
            ->with('error', $this->databaseFoutmelding($exception) ?? $fallbackMessage);
    }

    private function databaseFoutmelding(QueryException $exception): ?string
    {
        return $exception->errorInfo[2] ?? null;
    }
}
