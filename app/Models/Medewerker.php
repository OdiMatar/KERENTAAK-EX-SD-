<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Medewerker extends Model
{
    public const CREATED_AT = 'datum_aangemaakt';

    public const UPDATED_AT = 'datum_gewijzigd';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_EMPLOYEE = 'medewerker';

    public const ROLE_HAIRDRESSER = 'kapper';

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
     * @var array<string, bool>
     */
    private static array $columnCache = [];

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_EMPLOYEE => 'Medewerker',
            self::ROLE_HAIRDRESSER => 'Kapper',
            self::ROLE_INTERN => 'Stagiair',
        ];
    }

    public function afspraken(): HasMany
    {
        return $this->hasMany(Afspraak::class, 'medewerker_id');
    }

    /**
     * @return Collection<int, Medewerker>
     */
    public static function voorOverzicht(?string $role = null): Collection
    {
        $roles = self::roles();

        return self::query()
            ->leftJoin('gebruikers', 'gebruikers.id', '=', 'medewerkers.gebruiker_id')
            ->where('medewerkers.is_actief', true)
            ->when(
                $role !== null && $role !== '' && array_key_exists($role, $roles),
                fn ($query) => $query->where('medewerkers.functie', $roles[$role])
            )
            ->select([
                'medewerkers.id',
                'medewerkers.name',
                'medewerkers.gebruiker_id',
                'medewerkers.voornaam',
                'medewerkers.achternaam',
                'medewerkers.email',
                'medewerkers.role',
                'medewerkers.phone',
                'medewerkers.telefoonnummer',
                'medewerkers.functie',
                'medewerkers.is_active',
                'medewerkers.is_actief',
                'gebruikers.gebruikersnaam',
                DB::raw("CONCAT(medewerkers.voornaam, ' ', medewerkers.achternaam) AS volledige_naam"),
            ])
            ->orderBy('medewerkers.voornaam', 'asc')
            ->orderBy('medewerkers.achternaam', 'asc')
            ->get();
    }

    public function volledigeNaam(): string
    {
        $fullName = trim(($this->voornaam ?? '').' '.($this->achternaam ?? ''));

        return $fullName !== '' ? $fullName : '';
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
        $functie = strtolower((string) $this->functie);

        return match ($functie) {
            self::ROLE_MANAGER => self::ROLE_MANAGER,
            self::ROLE_HAIRDRESSER => self::ROLE_HAIRDRESSER,
            self::ROLE_INTERN => self::ROLE_INTERN,
            default => self::ROLE_EMPLOYEE,
        };
    }

    public function setNameAttribute(?string $value): void
    {
        $name = trim((string) $value);

        if (self::hasColumn('name')) {
            $this->attributes['name'] = $name;
        }

        if ($name !== '') {
            $this->attributes['voornaam'] = str($name)->before(' ')->toString();
            $achternaam = trim(str($name)->after(' ')->toString());
            $this->attributes['achternaam'] = $achternaam !== '' ? $achternaam : '-';
        }
    }

    public function setPhoneAttribute(?string $value): void
    {
        if (self::hasColumn('phone')) {
            $this->attributes['phone'] = $value;
        }

        $this->attributes['telefoonnummer'] = $value;
    }

    public function setRoleAttribute(?string $value): void
    {
        if (self::hasColumn('role')) {
            $this->attributes['role'] = $value;
        }

        $roles = self::roles();
        $this->attributes['functie'] = $roles[$value] ?? $roles[self::ROLE_EMPLOYEE];
    }

    public function setIsActiveAttribute(bool $value): void
    {
        if (self::hasColumn('is_active')) {
            $this->attributes['is_active'] = $value;
        }

        $this->attributes['is_actief'] = $value;
    }

    private static function hasColumn(string $column): bool
    {
        return self::$columnCache[$column] ??= Schema::hasColumn('medewerkers', $column);
    }

    protected static function booted(): void
    {
        static::saving(function (Medewerker $medewerker): void {
            $medewerker->is_actief ??= true;
        });
    }
}
