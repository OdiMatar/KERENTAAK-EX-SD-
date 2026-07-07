<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Klant extends Model
{
    public const CREATED_AT = 'datum_aangemaakt';

    public const UPDATED_AT = 'datum_gewijzigd';

    protected $table = 'klanten';

    protected $fillable = [
        'gebruiker_id',
        'voornaam',
        'achternaam',
        'adres',
        'telefoonnummer',
        'email',
        'is_actief',
        'opmerking',
    ];

    protected function casts(): array
    {
        return [
            'is_actief' => 'boolean',
        ];
    }

    public function getNaamAttribute(): string
    {
        return trim($this->voornaam.' '.$this->achternaam);
    }

    public function afspraken(): HasMany
    {
        return $this->hasMany(Afspraak::class, 'klant_id');
    }
}
