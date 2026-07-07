<?php

namespace App\Http\Controllers;

use App\Models\Bestelling;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BestellingController extends Controller
{
    private const BESTELLINGEN_PER_PAGINA = 4;

    private const PRODUCT_CATEGORIEEN = ['shampoo', 'conditioner', 'styling', 'verf', 'overig'];

    public function index(): View
    {
        $this->authorizeBestellingBeheer();

        $bestellingen = Bestelling::query()
            ->where('is_actief', true)
            ->latest('orderdatum')
            ->paginate(self::BESTELLINGEN_PER_PAGINA);

        return view('bestellingen.index', ['bestellingen' => $bestellingen]);
    }

    public function create(): View
    {
        $this->authorizeBestellingBeheer();

        return view('bestellingen.create', [
            'klanten' => $this->klanten(),
            'producten' => $this->producten(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        // Eerst valideren we alle velden voordat er iets in de database wordt opgeslagen.
        $data = $request->validate($this->storeRules());
        $product = $this->product((int) $data['product_id']);

        try {
            // De bestelling wordt eerst aangemaakt met totaalprijs 0.
            // Daarna wordt de eerste bestelregel toegevoegd en rekent het model het totaal opnieuw uit.
            $bestelling = Bestelling::query()->create([
                'klant_naam' => $data['klant_naam'],
                'orderdatum' => $data['orderdatum'],
                'verwachte_leverdatum' => $data['verwachte_leverdatum'],
                'status' => $data['status'],
                'opmerking' => $data['opmerking'] ?? null,
                'totaalprijs' => 0,
                'is_actief' => true,
            ]);

            $this->bewaarBestelregel($bestelling, $product, (int) $data['aantal']);
        } catch (Throwable) {
            return back()->withInput()->with('error', 'Bestelling kon niet worden toegevoegd.');
        }

        return redirect()
            ->route('bestellingen.show', $bestelling->id)
            ->with('status', 'Bestelling is toegevoegd.');
    }

    public function edit(Bestelling $bestelling): View
    {
        $this->authorizeBestellingBeheer();

        return view('bestellingen.edit', ['bestelling' => $bestelling]);
    }

    public function show(Bestelling $bestelling): View
    {
        $this->authorizeBestellingBeheer();

        return view('bestellingen.show', [
            'bestelling' => $bestelling,
            'bestelregels' => $this->bestelregels($bestelling),
            'producten' => $this->producten(),
        ]);
    }

    public function update(Request $request, Bestelling $bestelling): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        // Fill zet de nieuwe waarden alvast op het model, maar slaat nog niets op.
        $bestelling->fill($request->validate($this->bestellingRules()));

        if (! $bestelling->isDirty()) {
            return back()->withInput()->with('error', 'Bestelling is niet gewijzigd.');
        }

        try {
            $bestelling->save();
        } catch (Throwable) {
            return back()->withInput()->with('error', 'Bestelling kon niet worden gewijzigd.');
        }

        return redirect()
            ->route('bestellingen.index')
            ->with('status', 'Bestelling is gewijzigd.');
    }

    public function destroy(Bestelling $bestelling): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        // Bestellingen worden soft verwijderd met is_actief, zodat oude data bewaard blijft.
        if (! $bestelling->is_actief) {
            return back()->with('error', 'De bestelling kon niet verwijderd worden, omdat hij al verwijderd was.');
        }

        try {
            $bestelling->update(['is_actief' => false]);
        } catch (Throwable) {
            return back()->with('error', 'Bestelling kon niet worden verwijderd.');
        }

        return back()->with('status', 'Bestelling is verwijderd.');
    }

    public function storeRegel(Request $request, Bestelling $bestelling): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        $data = $request->validate($this->regelRules());
        $product = $this->product((int) $data['product_id']);

        try {
            $this->bewaarBestelregel($bestelling, $product, (int) $data['aantal']);
        } catch (Throwable) {
            return back()->withInput()->with('error', 'Product kon niet aan de bestelling worden toegevoegd.');
        }

        return back()->with('status', 'Product is toegevoegd aan de bestelling.');
    }

    public function updateRegel(Request $request, Bestelling $bestelling, int $bestelregel): RedirectResponse
    {
        $this->authorizeBestellingBeheer();
        $this->abortAlsRegelNietInBestellingZit($bestelling, $bestelregel);

        $data = $request->validate([
            'aantal' => ['required', 'integer', 'min:1'],
        ]);

        $regel = $this->bestelregel($bestelregel);

        try {
            // Het subtotaal hoort altijd gelijk te blijven aan aantal keer prijs per stuk.
            DB::table('bestelregels')->where('id', $bestelregel)->update([
                'aantal' => (int) $data['aantal'],
                'subtotaal' => $regel->prijs_per_stuk * (int) $data['aantal'],
            ]);
            $bestelling->updateTotaalprijs();
        } catch (Throwable) {
            return back()->withInput()->with('error', 'Aantal kon niet worden gewijzigd.');
        }

        return back()->with('status', 'Aantal is gewijzigd.');
    }

    public function destroyRegel(Bestelling $bestelling, int $bestelregel): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        if (! $this->regelZitInBestelling($bestelling, $bestelregel)) {
            return back()->with('error', 'Het product kon niet verwijderd worden, omdat hij al verwijderd was.');
        }

        try {
            DB::table('bestelregels')->where('id', $bestelregel)->delete();
            $bestelling->updateTotaalprijs();
        } catch (Throwable) {
            return back()->with('error', 'Product kon niet uit de bestelling worden verwijderd.');
        }

        return back()->with('status', 'Product is uit de bestelling verwijderd.');
    }

    public function createProduct(Bestelling $bestelling): View
    {
        $this->authorizeBestellingBeheer();

        return view('bestellingen.product-toevoegen', [
            'bestelling' => $bestelling,
            'categorieen' => self::PRODUCT_CATEGORIEEN,
        ]);
    }

    public function storeProduct(Request $request, Bestelling $bestelling): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        $data = $request->validate($this->nieuwProductRules());
        $data['is_actief'] = true;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        try {
            DB::table('products')->insert($data);
        } catch (Throwable) {
            return back()->withInput()->with('error', 'Product kon niet worden toegevoegd.');
        }

        return redirect()
            ->route('bestellingen.show', $bestelling->id)
            ->with('status', 'Product is toegevoegd.');
    }

    public function editProduct(Bestelling $bestelling, int $product): View
    {
        $this->authorizeBestellingBeheer();
        $this->abortAlsProductNietInBestellingZit($bestelling, $product);

        return view('bestellingen.product-wijzigen', [
            'bestelling' => $bestelling,
            'categorieen' => self::PRODUCT_CATEGORIEEN,
            'product' => $this->product($product),
        ]);
    }

    public function updateProduct(Request $request, Bestelling $bestelling, int $product): RedirectResponse
    {
        $this->authorizeBestellingBeheer();
        $this->abortAlsProductNietInBestellingZit($bestelling, $product);

        $data = $request->validate($this->productRules($product));
        $oudeProduct = $this->product($product);

        if (! $this->productIsGewijzigd($oudeProduct, $data)) {
            return back()->withInput()->with('error', 'Er zijn geen wijzigingen opgeslagen, omdat de productgegevens hetzelfde zijn gebleven.');
        }

        try {
            DB::table('products')->where('id', $product)->update($data);
            $this->updateBestelregelsVoorProduct($product, (float) $data['prijs']);
        } catch (Throwable) {
            return back()->withInput()->with('error', 'Product kon niet worden gewijzigd.');
        }

        return redirect()
            ->route('bestellingen.show', $bestelling->id)
            ->with('status', 'Product is gewijzigd.');
    }

    public function destroyProduct(Bestelling $bestelling, int $product): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        if (! $this->productZitInBestelling($bestelling, $product)) {
            return back()->with('error', 'Het product kon niet verwijderd worden, omdat hij al verwijderd was.');
        }

        $productData = $this->product($product);

        if (! $productData->is_actief) {
            return back()->with('error', 'Het product kon niet verwijderd worden, omdat hij al verwijderd was.');
        }

        try {
            DB::table('products')->where('id', $product)->update(['is_actief' => false]);
        } catch (Throwable) {
            return back()->with('error', 'Product kon niet worden verwijderd.');
        }

        return redirect()
            ->route('bestellingen.show', $bestelling->id)
            ->with('status', 'Product is verwijderd uit de voorraad.');
    }

    /**
     * Validatieregels voor het wijzigen van een bestaande bestelling.
     *
     * @return array<string, mixed>
     */
    private function bestellingRules(): array
    {
        return [
            'klant_naam' => ['required', 'string', 'max:150'],
            'orderdatum' => ['required', 'date'],
            'verwachte_leverdatum' => ['required', 'date', 'after_or_equal:today', 'after_or_equal:orderdatum'],
            'status' => ['required', 'string', 'max:50'],
            'opmerking' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Validatieregels voor het aanmaken van een bestelling.
     * Een nieuwe bestelling krijgt direct ook het eerste product mee.
     *
     * @return array<string, mixed>
     */
    private function storeRules(): array
    {
        return [
            ...$this->bestellingRules(),
            'product_id' => ['required', 'exists:products,id'],
            'aantal' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Validatieregels voor een losse bestelregel.
     * Een bestelregel is de koppeling tussen een bestelling en een product.
     *
     * @return array<string, mixed>
     */
    private function regelRules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'aantal' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Validatieregels voor het wijzigen van een product.
     * De EAN-code staat hier bewust niet tussen, omdat die niet gewijzigd mag worden.
     *
     * @return array<string, mixed>
     */
    private function productRules(int $productId): array
    {
        return [
            'naam' => ['required', 'string', 'max:150', Rule::unique('products', 'naam')->ignore($productId)],
            'categorie' => ['required', 'string', 'max:100'],
            'prijs' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'voorraad' => ['required', 'integer', 'min:0'],
            'leverancier' => ['required', 'string', 'max:150'],
            'is_actief' => ['required', 'boolean'],
        ];
    }

    /**
     * Validatieregels voor het aanmaken van een nieuw product.
     * Bij aanmaken is een EAN-code wel verplicht.
     *
     * @return array<string, mixed>
     */
    private function nieuwProductRules(): array
    {
        return [
            'naam' => ['required', 'string', 'max:150', Rule::unique('products', 'naam')],
            'categorie' => ['required', 'string', 'max:100'],
            'ean_code' => ['required', 'digits:13', Rule::unique('products', 'ean_code')],
            'prijs' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'voorraad' => ['required', 'integer', 'min:0'],
            'leverancier' => ['required', 'string', 'max:150'],
        ];
    }

    private function authorizeBestellingBeheer(): void
    {
        // Alleen eigenaars en medewerkers mogen bestellingen beheren.
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->isOwner() || $user?->isEmployee(), 403);
    }

    private function abortAlsProductNietInBestellingZit(Bestelling $bestelling, int $product): void
    {
        // Voorkomt dat een product via een verkeerde bestelling wordt aangepast.
        abort_unless($this->productZitInBestelling($bestelling, $product), 404);
    }

    private function abortAlsRegelNietInBestellingZit(Bestelling $bestelling, int $bestelregel): void
    {
        // Voorkomt dat een bestelregel van een andere bestelling wordt aangepast.
        abort_unless($this->regelZitInBestelling($bestelling, $bestelregel), 404);
    }

    private function productZitInBestelling(Bestelling $bestelling, int $product): bool
    {
        return DB::table('bestelregels')
            ->where('bestelling_id', $bestelling->id)
            ->where('product_id', $product)
            ->exists();
    }

    private function regelZitInBestelling(Bestelling $bestelling, int $bestelregel): bool
    {
        return DB::table('bestelregels')
            ->where('id', $bestelregel)
            ->where('bestelling_id', $bestelling->id)
            ->exists();
    }

    private function bewaarBestelregel(Bestelling $bestelling, object $product, int $aantal): void
    {
        // Controleer eerst of dit product al in dezelfde bestelling staat.
        $bestaandeRegel = DB::table('bestelregels')
            ->where('bestelling_id', $bestelling->id)
            ->where('product_id', $product->id)
            ->first();

        $huidigAantal = $bestaandeRegel ? (int) $bestaandeRegel->aantal : 0;
        $nieuwAantal = $huidigAantal + $aantal;

        $regelData = [
            'aantal' => $nieuwAantal,
            'prijs_per_stuk' => $product->prijs,
            'subtotaal' => $nieuwAantal * $product->prijs,
        ];

        if ($bestaandeRegel) {
            // Als de regel al bestaat, verhogen we alleen het aantal en subtotaal.
            DB::table('bestelregels')->where('id', $bestaandeRegel->id)->update($regelData);
            $bestelling->updateTotaalprijs();

            return;
        }

        // Als de regel nog niet bestaat, maken we een nieuwe koppeling aan.
        $regelData['bestelling_id'] = $bestelling->id;
        $regelData['product_id'] = $product->id;

        DB::table('bestelregels')->insert($regelData);
        $bestelling->updateTotaalprijs();
    }

    private function updateBestelregelsVoorProduct(int $product, float $prijs): void
    {
        // Eerst bewaren we welke bestellingen opnieuw berekend moeten worden.
        $bestellingIds = DB::table('bestelregels')
            ->where('product_id', $product)
            ->pluck('bestelling_id')
            ->unique();

        $bestelregels = DB::table('bestelregels')
            ->where('product_id', $product)
            ->get();

        foreach ($bestelregels as $regel) {
            // Elke bestaande bestelregel krijgt de nieuwe productprijs.
            DB::table('bestelregels')->where('id', $regel->id)->update([
                'prijs_per_stuk' => $prijs,
                'subtotaal' => $regel->aantal * $prijs,
            ]);
        }

        $bestellingen = Bestelling::query()
            ->whereIn('id', $bestellingIds)
            ->get();

        foreach ($bestellingen as $bestelling) {
            // Na prijswijzigingen moet het totaalbedrag opnieuw worden berekend.
            $bestelling->updateTotaalprijs();
        }
    }

    /**
     * Vergelijkt alle ingevulde velden met het bestaande product.
     * Zodra één veld anders is, is het product gewijzigd.
     *
     * @param  array<string, mixed>  $data
     */
    private function productIsGewijzigd(object $product, array $data): bool
    {
        foreach ($data as $veld => $nieuweWaarde) {
            $oudeWaarde = $product->{$veld};

            if ((string) $oudeWaarde !== (string) $nieuweWaarde) {
                return true;
            }
        }

        return false;
    }

    private function klanten()
    {
        // Klanten komen uit bestaande actieve bestellingen.
        return Bestelling::query()
            ->where('is_actief', true)
            ->select('klant_naam')
            ->distinct()
            ->orderBy('klant_naam')
            ->pluck('klant_naam');
    }

    private function producten()
    {
        // Alleen actieve producten mogen gekozen worden in nieuwe bestelregels.
        return DB::table('products')
            ->where('is_actief', true)
            ->orderBy('naam')
            ->get();
    }

    private function product(int $product): object
    {
        return DB::table('products')->where('id', $product)->firstOrFail();
    }

    private function bestelregel(int $bestelregel): object
    {
        return DB::table('bestelregels')->where('id', $bestelregel)->firstOrFail();
    }

    private function bestelregels(Bestelling $bestelling)
    {
        // JOIN: bestelregels bevat aantallen en subtotaal, products bevat productinformatie.
        // Door de tabellen te joinen kan de view alles in één overzicht tonen.
        return DB::table('bestelregels')
            ->join('products', 'products.id', '=', 'bestelregels.product_id')
            ->where('bestelregels.bestelling_id', $bestelling->id)
            ->select(
                'bestelregels.*',
                'products.naam as product_naam',
                'products.categorie',
                'products.ean_code',
                'products.voorraad',
                'products.leverancier'
            )
            ->orderBy('bestelregels.id')
            ->get();
    }
}
