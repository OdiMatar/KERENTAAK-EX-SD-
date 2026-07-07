<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKlantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'naam' => ['required', 'string', 'max:200'],
            'adres' => ['required', 'string', 'max:255'],
            'telefoonnummer' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150'],
            'is_actief' => ['required', 'boolean'],
            'wensen' => ['nullable', 'string', 'max:255'],
            'allergieen' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'naam.required' => 'Naam is een verplicht veld en mag niet leeg zijn',
            'adres.required' => 'Adres is een verplicht veld en mag niet leeg zijn',
            'email.email' => 'Vul een geldig e-mailadres in',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $adres = trim((string) $this->input('adres'));

            if ($adres === '' || $this->adresHeeftAlleOnderdelen($adres)) {
                return;
            }

            $validator->errors()->add(
                'adres',
                'Straatnaam, huisnummer, postcode en stad zijn verplicht om te vullen.'
            );
        });
    }

    private function adresHeeftAlleOnderdelen(string $adres): bool
    {
        $heeftPostcode = preg_match('/\b[1-9][0-9]{3}\s?[A-Z]{2}\b/i', $adres) === 1;
        $adresZonderPostcode = preg_replace('/\b[1-9][0-9]{3}\s?[A-Z]{2}\b/i', '', $adres) ?? $adres;
        $heeftHuisnummer = preg_match('/\b[0-9]+[A-Z]?\b/i', $adresZonderPostcode) === 1;
        $heeftStraat = preg_match('/[A-ZÀ-ÿ]{2,}.*\b[0-9]+[A-Z]?\b/i', $adresZonderPostcode) === 1;
        $heeftStad = preg_match('/\b[1-9][0-9]{3}\s?[A-Z]{2}\b[\s,]+[A-ZÀ-ÿ][A-ZÀ-ÿ\s-]{1,}$/i', $adres) === 1;

        return $heeftStraat && $heeftHuisnummer && $heeftPostcode && $heeftStad;
    }
}
