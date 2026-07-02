<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $this->authorizeProductManagement();

        $products = Product::query()
            ->where('is_actief', true)
            ->orderBy('naam')
            ->get();

        return view('products.index', ['products' => $products]);
    }

    public function create(): View
    {
        $this->authorizeProductManagement();

        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeProductManagement();

        $validated = $request->validate($this->rules());

        if ($this->productExists($validated['naam'], $validated['barcode'])) {
            return back()->withInput()->with('error', 'Product is niet toegevoegd.');
        }

        Product::query()->create([
            ...$validated,
            'categorie_id' => $this->defaultCategoryId(),
            'leverancier_id' => $this->defaultSupplierId(),
            'status' => 'Beschikbaar',
            'is_actief' => true,
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product is toegevoegd.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeProductManagement();

        return view('products.edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProductManagement();

        $validated = $request->validate($this->rules($product));
        $product->fill($validated);

        if (! $product->isDirty()) {
            return back()->withInput()->with('error', 'Product is niet gewijzigd.');
        }

        $product->save();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product is gewijzigd.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProductManagement();

        if (! $product->is_actief) {
            return back()->with('error', 'Product was al verwijderd.');
        }

        $product->update([
            'is_actief' => false,
            'status' => 'Niet beschikbaar',
        ]);

        return back()->with('status', 'Product is verwijderd.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Product $product = null): array
    {
        return [
            'naam' => ['required', 'string', 'max:150'],
            'barcode' => array_filter([
                'required',
                'string',
                'max:20',
                $product ? Rule::unique('products', 'barcode')->ignore($product->id) : null,
            ]),
            'prijs' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'voorraad' => ['required', 'integer', 'min:0'],
            'houdbaarheidsdatum' => ['nullable', 'date'],
            'omschrijving' => ['nullable', 'string', 'max:255'],
            'opmerking' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function productExists(string $name, string $barcode): bool
    {
        return Product::query()
            ->where('naam', $name)
            ->orWhere('barcode', $barcode)
            ->exists();
    }

    private function defaultCategoryId(): int
    {
        DB::table('categories')->updateOrInsert(
            ['naam' => 'Algemeen'],
            ['omschrijving' => 'Standaardcategorie voor producten', 'is_actief' => true],
        );

        return (int) DB::table('categories')->where('naam', 'Algemeen')->value('id');
    }

    private function defaultSupplierId(): int
    {
        DB::table('leveranciers')->updateOrInsert(
            ['naam' => 'Kniploket Tiko'],
            ['contactpersoon' => 'Lisa Jansen', 'email' => 'info@kniplokettiko.nl', 'is_actief' => true],
        );

        return (int) DB::table('leveranciers')->where('naam', 'Kniploket Tiko')->value('id');
    }

    private function authorizeProductManagement(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->isOwner() || $user?->isEmployee(), 403);
    }
}
