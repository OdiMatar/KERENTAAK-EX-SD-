<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medewerker extends Model
{
    public const CREATED_AT = 'datum_aangemaakt';

    public const UPDATED_AT = 'datum_gewijzigd';

    protected $table = 'medewerkers';

    public function afspraken(): HasMany
    {
        return $this->hasMany(Afspraak::class, 'medewerker_id');
    }

    public function volledigeNaam(): string
    {
        return trim($this->voornaam.' '.$this->achternaam);
    }
}
