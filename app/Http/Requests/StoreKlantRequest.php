<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
