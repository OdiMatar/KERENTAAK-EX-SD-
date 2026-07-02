<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['gebruiker_id', 'voornaam', 'achternaam', 'telefoonnummer', 'email'])]
class Klant extends Model
{
    public const CREATED_AT = 'datum_aangemaakt';

    public const UPDATED_AT = 'datum_gewijzigd';

    protected $table = 'klanten';

    public function afspraken(): HasMany
    {
        return $this->hasMany(Afspraak::class, 'klant_id');
    }
}
