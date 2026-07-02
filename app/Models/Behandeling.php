<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Behandeling extends Model
{
    public const CREATED_AT = 'datum_aangemaakt';

    public const UPDATED_AT = 'datum_gewijzigd';

    protected $table = 'behandelingen';
}
