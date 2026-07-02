<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medewerker extends Model
{
    public const ROLE_EMPLOYEE = 'medewerker';
    public const ROLE_INTERN = 'stagair';
    public const ROLE_VOLUNTEER = 'vrijwilliger';

    protected $fillable = [
        'name',
        'email',
        'role',
        'phone',
    ];

    public static function roles(): array
    {
        return [
            self::ROLE_EMPLOYEE => 'Medewerker',
            self::ROLE_INTERN => 'Stagiair',
            self::ROLE_VOLUNTEER => 'Vrijwilliger',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
