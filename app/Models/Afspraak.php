<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Afspraak extends Model
{
    public const CREATED_AT = 'datum_aangemaakt';

    public const UPDATED_AT = 'datum_gewijzigd';

    protected $table = 'afspraken';

    public function klant(): BelongsTo
    {
        return $this->belongsTo(Klant::class, 'klant_id');
    }

    public function medewerker(): BelongsTo
    {
        return $this->belongsTo(Medewerker::class, 'medewerker_id');
    }
}
