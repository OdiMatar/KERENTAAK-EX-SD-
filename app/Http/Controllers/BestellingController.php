<?php

namespace App\Http\Controllers;

use App\Models\Bestelling;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        $data = $request->validate($this->storeRules());
        $product = $this->product((int) $data['product_id']);

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

        $bestelling->fill($request->validate($this->bestellingRules()));

        if (! $bestelling->isDirty()) {
            return back()->withInput()->with('error', 'Bestelling is niet gewijzigd.');
        }

        $bestelling->save();

        return redirect()
            ->route('bestellingen.index')
            ->with('status', 'Bestelling is gewijzigd.');
    }

    public function destroy(Bestelling $bestelling): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        if (! $bestelling->is_actief) {
            return back()->with('error', 'De bestelling kon niet verwijderd worden, omdat hij al verwijderd was.');
        }

        $bestelling->update(['is_actief' => false]);

        return back()->with('status', 'Bestelling is verwijderd.');
    }

    public function storeRegel(Request $request, Bestelling $bestelling): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        $data = $request->validate($this->regelRules());
        $product = $this->product((int) $data['product_id']);

        $this->bewaarBestelregel($bestelling, $product, (int) $data['aantal']);

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

        DB::table('bestelregels')->where('id', $bestelregel)->update([
            'aantal' => (int) $data['aantal'],
            'subtotaal' => $regel->prijs_per_stuk * (int) $data['aantal'],
        ]);
        $bestelling->updateTotaalprijs();

        return back()->with('status', 'Aantal is gewijzigd.');
    }

    public function destroyRegel(Bestelling $bestelling, int $bestelregel): RedirectResponse
    {
        $this->authorizeBestellingBeheer();

        if (! $this->regelZitInBestelling($bestelling, $bestelregel)) {
            return back()->with('error', 'Het product kon niet verwijderd worden, omdat hij al verwijderd was.');
        }

        DB::table('bestelregels')->where('id', $bestelregel)->delete();
        $bestelling->updateTotaalprijs();

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

        DB::table('products')->insert([
            ...$request->validate($this->nieuwProductRules()),
            'is_actief' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        DB::table('products')->where('id', $product)->update($data);
        $this->updateBestelregelsVoorProduct($product, (float) $data['prijs']);

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

        DB::table('products')->where('id', $product)->update(['is_actief' => false]);

        return redirect()
            ->route('bestellingen.show', $bestelling->id)
            ->with('status', 'Product is verwijderd uit de voorraad.');
    }

    /**
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
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->isOwner() || $user?->isEmployee(), 403);
    }

    private function abortAlsProductNietInBestellingZit(Bestelling $bestelling, int $product): void
    {
        abort_unless($this->productZitInBestelling($bestelling, $product), 404);
    }

    private function abortAlsRegelNietInBestellingZit(Bestelling $bestelling, int $bestelregel): void
    {
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
        $regel = DB::table('bestelregels')
            ->where('bestelling_id', $bestelling->id)
            ->where('product_id', $product->id)
            ->first();

        $nieuwAantal = ((int) ($regel->aantal ?? 0)) + $aantal;
        $data = [
            'aantal' => $nieuwAantal,
            'prijs_per_stuk' => $product->prijs,
            'subtotaal' => $nieuwAantal * $product->prijs,
        ];

        if ($regel) {
            DB::table('bestelregels')->where('id', $regel->id)->update($data);
        } else {
            DB::table('bestelregels')->insert([
                ...$data,
                'bestelling_id' => $bestelling->id,
                'product_id' => $product->id,
            ]);
        }

        $bestelling->updateTotaalprijs();
    }

    private function updateBestelregelsVoorProduct(int $product, float $prijs): void
    {
        $bestellingIds = DB::table('bestelregels')
            ->where('product_id', $product)
            ->pluck('bestelling_id')
            ->unique();

        DB::table('bestelregels')
            ->where('product_id', $product)
            ->get()
            ->each(fn (object $regel) => DB::table('bestelregels')->where('id', $regel->id)->update([
                'prijs_per_stuk' => $prijs,
                'subtotaal' => $regel->aantal * $prijs,
            ]));

        Bestelling::query()
            ->whereIn('id', $bestellingIds)
            ->get()
            ->each(fn (Bestelling $bestelling) => $bestelling->updateTotaalprijs());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function productIsGewijzigd(object $product, array $data): bool
    {
        return collect($data)->contains(
            fn ($waarde, $veld) => (string) $product->{$veld} !== (string) $waarde
        );
    }

    private function klanten()
    {
        return Bestelling::query()
            ->where('is_actief', true)
            ->select('klant_naam')
            ->distinct()
            ->orderBy('klant_naam')
            ->pluck('klant_naam');
    }

    private function producten()
    {
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
