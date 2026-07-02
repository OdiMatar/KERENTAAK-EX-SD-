<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKlantRequest;
use App\Http\Requests\UpdateKlantRequest;
use App\Models\Klant;
use App\Models\User;
use App\Services\TechnicalLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class KlantController extends Controller
{
    public function index(Request $request, TechnicalLogger $technicalLogger): View
    {
        $this->authorizeKlantBeheer();

        $zoekterm = trim((string) $request->query('zoekterm', ''));
        $klanten = $this->klantenVoorOverzicht($zoekterm);

        $technicalLogger->record('customer_index', 'Klantenoverzicht geopend.', auth()->id(), [
            'search' => $zoekterm,
            'customers_count' => $klanten->count(),
        ]);

        return view('klanten.index', [
            'klanten' => $klanten,
            'zoekterm' => $zoekterm,
        ]);
    }

    public function create(): View
    {
        $this->authorizeKlantBeheer();

        return view('klanten.create', [
            'bestaandeKlanten' => $this->bestaandeKlantCombinaties(),
        ]);
    }

    public function store(StoreKlantRequest $request, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $this->authorizeKlantBeheer();

        $data = $this->klantData($request->validated());

        try {
            $customerId = $this->maakKlant($data);

            $technicalLogger->record('customer_create', 'Klant toegevoegd.', auth()->id(), [
                'customer_id' => $customerId,
                'email' => $data['email'],
            ]);

            return redirect()
                ->route('klanten.index')
                ->with('status', 'Klant met succes toegevoegd');
        } catch (QueryException $exception) {
            $technicalLogger->record('customer_create_failed', 'Klant toevoegen mislukt.', auth()->id(), [
                'email' => $data['email'],
                'error' => $this->databaseFoutmelding($exception),
            ]);

            return $this->terugMetDatabaseFout($exception, 'Klant toevoegen is niet gelukt.');
        } catch (Throwable $exception) {
            Log::error('Klant toevoegen mislukt.', ['exception' => $exception]);

            return back()
                ->withInput()
                ->with('error', 'Klant toevoegen is niet gelukt.');
        }
    }

    public function edit(Klant $klant): View
    {
        $this->authorizeKlantBeheer();

        return view('klanten.edit', [
            'klant' => $klant,
            'bestaandeKlanten' => $this->bestaandeKlantCombinaties($klant->id),
            'wensen' => $this->wensenVoorKlant($klant->id, 'wens')->implode(', '),
            'allergieen' => $this->wensenVoorKlant($klant->id, 'allergie')->implode(', '),
        ]);
    }

    public function show(Klant $klant, TechnicalLogger $technicalLogger): View
    {
        $this->authorizeKlantBeheer();

        $technicalLogger->record('customer_show', 'Klantdetail geopend.', auth()->id(), [
            'customer_id' => $klant->id,
        ]);

        return view('klanten.show', [
            'klant' => $klant,
            'historie' => $this->historieVoorKlant($klant),
            'wensen' => $this->wensenVoorKlant($klant->id, 'wens'),
            'allergieen' => $this->wensenVoorKlant($klant->id, 'allergie'),
        ]);
    }

    public function update(UpdateKlantRequest $request, Klant $klant, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $this->authorizeKlantBeheer();

        $data = $this->klantData($request->validated());

        try {
            if (! $this->klantIsGewijzigd($klant, $data)) {
                $technicalLogger->record('customer_update_unchanged', 'Klant wijzigen zonder wijzigingen.', auth()->id(), [
                    'customer_id' => $klant->id,
                ]);

                return back()
                    ->withInput()
                    ->with('status', 'Er is niks gewijzigd');
            }

            $this->wijzigKlant($klant, $data);

            $technicalLogger->record('customer_update', 'Klant gewijzigd.', auth()->id(), [
                'customer_id' => $klant->id,
                'email' => $data['email'],
            ]);

            return redirect()
                ->route('klanten.index')
                ->with('status', 'Klant met succes gewijzigd');
        } catch (QueryException $exception) {
            $technicalLogger->record('customer_update_failed', 'Klant wijzigen mislukt.', auth()->id(), [
                'customer_id' => $klant->id,
                'error' => $this->databaseFoutmelding($exception),
            ]);

            return $this->terugMetDatabaseFout($exception, 'Klant wijzigen is niet gelukt.');
        } catch (Throwable $exception) {
            Log::error('Klant wijzigen mislukt.', ['customer_id' => $klant->id, 'exception' => $exception]);

            return back()
                ->withInput()
                ->with('error', 'Klant wijzigen is niet gelukt.');
        }
    }

    public function destroy(Klant $klant, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $this->authorizeKlantBeheer();

        try {
            $this->verwijderKlant($klant);

            $technicalLogger->record('customer_delete', 'Klant verwijderd.', auth()->id(), [
                'customer_id' => $klant->id,
                'email' => $klant->email,
            ]);

            return redirect()
                ->route('klanten.index')
                ->with('status', 'Klant met succes verwijderd');
        } catch (QueryException $exception) {
            $technicalLogger->record('customer_delete_failed', 'Klant verwijderen mislukt.', auth()->id(), [
                'customer_id' => $klant->id,
                'error' => $this->databaseFoutmelding($exception),
            ]);

            return back()->with('error', $this->databaseFoutmelding($exception) ?? 'Klant verwijderen is niet gelukt.');
        } catch (Throwable $exception) {
            Log::error('Klant verwijderen mislukt.', ['customer_id' => $klant->id, 'exception' => $exception]);

            return back()->with('error', $exception->getMessage() ?: 'Klant verwijderen is niet gelukt.');
        }
    }

    private function authorizeKlantBeheer(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->isOwner() || $user?->isEmployee(), 403);
    }

    /**
     * @return Collection<int, object|Klant>
     */
    private function klantenVoorOverzicht(string $zoekterm): Collection
    {
        if ($this->gebruiktMysql()) {
            return collect(DB::select('CALL sp_get_customers(?)', [$zoekterm]));
        }

        return Klant::query()
            ->leftJoin('gebruikers', 'gebruikers.id', '=', 'klanten.gebruiker_id')
            ->where('klanten.is_actief', true)
            ->when($zoekterm !== '', function ($query) use ($zoekterm): void {
                $query->where(function ($query) use ($zoekterm): void {
                    $query
                        ->where('klanten.voornaam', 'like', "%{$zoekterm}%")
                        ->orWhere('klanten.achternaam', 'like', "%{$zoekterm}%");
                });
            })
            ->select([
                'klanten.id',
                'klanten.voornaam',
                'klanten.achternaam',
                'klanten.adres',
                'klanten.telefoonnummer',
                'klanten.email',
                'gebruikers.gebruikersnaam',
            ])
            ->orderBy('klanten.voornaam')
            ->orderBy('klanten.achternaam')
            ->get();
    }

    /**
     * @param array<string, string|null> $data
     */
    private function maakKlant(array $data): int
    {
        if ($this->gebruiktMysql()) {
            $result = DB::selectOne('CALL sp_create_customer(?, ?, ?, ?, ?, ?, ?)', [
                $data['voornaam'],
                $data['achternaam'],
                $data['adres'],
                $data['telefoonnummer'],
                $data['email'],
                $data['wensen'],
                $data['allergieen'],
            ]);

            return (int) $result->customer_id;
        }

        $klant = Klant::query()->create($this->alleenKlantVelden($data));
        $this->bewaarWensenVoorKlant((int) $klant->id, $data['wensen'], $data['allergieen']);

        return (int) $klant->id;
    }

    /**
     * @param array<string, string|null> $data
     */
    private function wijzigKlant(Klant $klant, array $data): void
    {
        if ($this->gebruiktMysql()) {
            DB::select('CALL sp_update_customer(?, ?, ?, ?, ?, ?, ?, ?)', [
                $klant->id,
                $data['voornaam'],
                $data['achternaam'],
                $data['adres'],
                $data['telefoonnummer'],
                $data['email'],
                $data['wensen'],
                $data['allergieen'],
            ]);

            return;
        }

        $klant->update($this->alleenKlantVelden($data));
        $this->bewaarWensenVoorKlant($klant->id, $data['wensen'], $data['allergieen']);
    }

    private function verwijderKlant(Klant $klant): void
    {
        if ($this->gebruiktMysql()) {
            DB::select('CALL sp_delete_customer(?)', [$klant->id]);

            return;
        }

        if ($klant->afspraken()->exists()) {
            throw new \RuntimeException('Deze klant kan niet worden verwijderd omdat er afspraken aan gekoppeld zijn');
        }

        $klant->delete();
    }

    /**
     * @param array<string, string|null> $data
     * @return array<string, string|null>
     */
    private function klantData(array $data): array
    {
        $naam = trim((string) $data['naam']);
        $naamDelen = preg_split('/\s+/', $naam, 2);

        return [
            'voornaam' => $naamDelen[0],
            'achternaam' => $naamDelen[1] ?? '-',
            'adres' => $data['adres'],
            'telefoonnummer' => $data['telefoonnummer'] ?? null,
            'email' => $data['email'] ?? null,
            'wensen' => $data['wensen'] ?? null,
            'allergieen' => $data['allergieen'] ?? null,
        ];
    }

    /**
     * @param array<string, string|null> $data
     * @return array<string, string|null>
     */
    private function alleenKlantVelden(array $data): array
    {
        return [
            'voornaam' => $data['voornaam'],
            'achternaam' => $data['achternaam'],
            'adres' => $data['adres'],
            'telefoonnummer' => $data['telefoonnummer'],
            'email' => $data['email'],
        ];
    }

    /**
     * @param array<string, string|null> $data
     */
    private function klantIsGewijzigd(Klant $klant, array $data): bool
    {
        $huidigeWaarden = [
            'voornaam' => $klant->voornaam,
            'achternaam' => $klant->achternaam,
            'adres' => $klant->adres,
            'telefoonnummer' => $klant->telefoonnummer,
            'email' => $klant->email,
            'wensen' => $this->wensenVoorKlant($klant->id, 'wens')->implode(', '),
            'allergieen' => $this->wensenVoorKlant($klant->id, 'allergie')->implode(', '),
        ];

        foreach ($huidigeWaarden as $veld => $huidigeWaarde) {
            if ($this->normaliseerVergelijkingswaarde($huidigeWaarde) !== $this->normaliseerVergelijkingswaarde($data[$veld] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function normaliseerVergelijkingswaarde(?string $waarde): string
    {
        return trim((string) $waarde);
    }

    /**
     * @return Collection<int, object>
     */
    private function historieVoorKlant(Klant $klant): Collection
    {
        if ($this->gebruiktMysql()) {
            return collect(DB::select('CALL sp_get_customer_history(?)', [$klant->id]));
        }

        $behandelingen = DB::table('afspraken')
            ->join('afspraak_behandeling', 'afspraak_behandeling.afspraak_id', '=', 'afspraken.id')
            ->join('behandelingen', 'behandelingen.id', '=', 'afspraak_behandeling.behandeling_id')
            ->join('medewerkers', 'medewerkers.id', '=', 'afspraken.medewerker_id')
            ->where('afspraken.klant_id', $klant->id)
            ->selectRaw("'behandeling' as type, behandelingen.naam as titel, afspraken.datum as datum, afspraken.status as status, CONCAT(medewerkers.voornaam, ' ', medewerkers.achternaam) as extra");

        $producten = DB::table('bestellingen')
            ->join('bestelregels', 'bestelregels.bestelling_id', '=', 'bestellingen.id')
            ->join('products', 'products.id', '=', 'bestelregels.product_id')
            ->where(function ($query) use ($klant): void {
                $query
                    ->where('bestellingen.klant_id', $klant->id)
                    ->orWhere('bestellingen.klant_naam', $klant->naam);
            })
            ->selectRaw("'product' as type, products.naam as titel, bestellingen.orderdatum as datum, bestellingen.status as status, CONCAT(bestelregels.aantal, ' x ', products.categorie) as extra");

        return $behandelingen
            ->unionAll($producten)
            ->orderByDesc('datum')
            ->get();
    }

    /**
     * @return Collection<int, string>
     */
    private function wensenVoorKlant(int $klantId, string $type): Collection
    {
        return DB::table('klant_wensen')
            ->join('wens_allergies', 'wens_allergies.id', '=', 'klant_wensen.wens_allergie_id')
            ->where('klant_wensen.klant_id', $klantId)
            ->where('wens_allergies.type', $type)
            ->where('wens_allergies.is_actief', true)
            ->orderBy('wens_allergies.beschrijving')
            ->pluck('wens_allergies.beschrijving');
    }

    private function bewaarWensenVoorKlant(int $klantId, ?string $wensen, ?string $allergieen): void
    {
        $this->vervangWensType($klantId, 'wens', $wensen);
        $this->vervangWensType($klantId, 'allergie', $allergieen);
    }

    private function vervangWensType(int $klantId, string $type, ?string $beschrijving): void
    {
        DB::table('klant_wensen')
            ->join('wens_allergies', 'wens_allergies.id', '=', 'klant_wensen.wens_allergie_id')
            ->where('klant_wensen.klant_id', $klantId)
            ->where('wens_allergies.type', $type)
            ->delete();

        if ($beschrijving === null || trim($beschrijving) === '') {
            return;
        }

        $wensId = DB::table('wens_allergies')->insertGetId([
            'type' => $type,
            'beschrijving' => trim($beschrijving),
            'is_actief' => true,
            'datum_aangemaakt' => now(),
            'datum_gewijzigd' => now(),
        ]);

        DB::table('klant_wensen')->insert([
            'klant_id' => $klantId,
            'wens_allergie_id' => $wensId,
            'datum_aangemaakt' => now(),
        ]);
    }

    /**
     * @return array<int, array{email: string, adres: string}>
     */
    private function bestaandeKlantCombinaties(?int $uitgeslotenKlantId = null): array
    {
        return Klant::query()
            ->where('is_actief', true)
            ->when($uitgeslotenKlantId !== null, fn ($query) => $query->whereKeyNot($uitgeslotenKlantId))
            ->whereNotNull('email')
            ->whereNotNull('adres')
            ->get(['email', 'adres'])
            ->map(fn (Klant $klant): array => [
                'email' => strtolower($klant->email),
                'adres' => strtolower($klant->adres),
            ])
            ->values()
            ->all();
    }

    private function gebruiktMysql(): bool
    {
        return DB::getDriverName() === 'mysql';
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
