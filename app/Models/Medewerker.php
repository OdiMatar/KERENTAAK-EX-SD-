<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medewerker extends Model
{
    public const ROLE_MANAGER = 'manager';

    public const ROLE_EMPLOYEE = 'medewerker';

    public const ROLE_INTERN = 'stagiair';

    protected $table = 'medewerkers';

    protected $fillable = [
        'name',
        'gebruiker_id',
        'voornaam',
        'achternaam',
        'email',
        'role',
        'phone',
        'telefoonnummer',
        'functie',
        'is_active',
        'is_actief',
    ];

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_EMPLOYEE => 'Medewerker',
            self::ROLE_INTERN => 'Stagiair',
        ];
    }

    public function afspraken(): HasMany
    {
        return $this->hasMany(Afspraak::class, 'medewerker_id');
    }

    public function volledigeNaam(): string
    {
        $fullName = trim(($this->voornaam ?? '').' '.($this->achternaam ?? ''));

        return $fullName !== '' ? $fullName : $this->name;
    }

    protected static function booted(): void
    {
        static::saving(function (Medewerker $medewerker): void {
            if (! $medewerker->voornaam && $medewerker->name) {
                $medewerker->voornaam = str($medewerker->name)->before(' ')->toString();
            }

            if (! $medewerker->achternaam && $medewerker->name) {
                $achternaam = trim(str($medewerker->name)->after(' ')->toString());
                $medewerker->achternaam = $achternaam !== '' ? $achternaam : '-';
            }

            if (! $medewerker->telefoonnummer && $medewerker->phone) {
                $medewerker->telefoonnummer = $medewerker->phone;
            }

            if (! $medewerker->functie && $medewerker->role) {
                $medewerker->functie = ucfirst($medewerker->role);
            }

            $medewerker->is_actief = (bool) $medewerker->is_active;
            $medewerker->datum_aangemaakt ??= now();
            $medewerker->datum_gewijzigd = now();
        });
    }
}
