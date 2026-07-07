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
        'gebruiker_id',
        'voornaam',
        'achternaam',
        'email',
        'telefoonnummer',
        'functie',
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

        return $fullName !== '' ? $fullName : ($this->attributes['name'] ?? '');
    }

    public function getNameAttribute(): string
    {
        return $this->volledigeNaam();
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->telefoonnummer;
    }

    public function getRoleAttribute(): string
    {
        $functie = strtolower($this->functie ?? '');

        return match ($functie) {
            'manager' => self::ROLE_MANAGER,
            'stagiair' => self::ROLE_INTERN,
            default => self::ROLE_EMPLOYEE,
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Medewerker $medewerker): void {
            $medewerker->is_actief ??= true;
            $medewerker->datum_aangemaakt ??= now();
            $medewerker->datum_gewijzigd = now();
        });
    }
}
