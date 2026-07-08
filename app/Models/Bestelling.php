<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'klant_naam',
    'orderdatum',
    'verwachte_leverdatum',
    'status',
    'totaalprijs',
    'opmerking',
    'is_actief',
])]
class Bestelling extends Model
{
    // Dit model hoort bij de tabel bestellingen.
    protected $table = 'bestellingen';

    protected function casts(): array
    {
        // Casts zorgen dat datums, bedragen en booleans in PHP meteen het juiste type hebben.
        return [
            'orderdatum' => 'date',
            'verwachte_leverdatum' => 'date',
            'totaalprijs' => 'decimal:2',
            'is_actief' => 'boolean',
        ];
    }

    public function updateTotaalprijs(): void
    {
        // De totaalprijs wordt altijd opnieuw berekend vanuit de bestelregels.
        // Daardoor blijft het totaal correct na toevoegen, verwijderen of wijzigen van producten.
        $this->update([
            'totaalprijs' => DB::table('bestelregels')
                ->where('bestelling_id', $this->id)
                ->sum('subtotaal'),
        ]);
    }
}
