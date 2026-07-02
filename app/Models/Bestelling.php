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
    protected $table = 'bestellingen';

    protected function casts(): array
    {
        return [
            'orderdatum' => 'date',
            'verwachte_leverdatum' => 'date',
            'totaalprijs' => 'decimal:2',
            'is_actief' => 'boolean',
        ];
    }

    public function updateTotaalprijs(): void
    {
        $this->update([
            'totaalprijs' => DB::table('bestelregels')
                ->where('bestelling_id', $this->id)
                ->sum('subtotaal'),
        ]);
    }
}
