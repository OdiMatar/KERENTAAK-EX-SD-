<?php

namespace App\Http\Requests;

use App\Models\Medewerker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedewerkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:medewerkers,email'],
            'role' => ['required', 'string', 'max:50', Rule::in(array_keys(Medewerker::roles()))],
            'phone' => ['nullable', 'digits:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vul de naam van de medewerker in',
            'name.string' => 'De naam van de medewerker moet tekst zijn',
            'name.max' => 'De naam van de medewerker mag maximaal 255 tekens bevatten',
            'email.required' => 'Vul het e-mailadres van de medewerker in',
            'email.email' => 'Vul een geldig e-mailadres in, bijvoorbeeld naam@voorbeeld.nl',
            'email.max' => 'Het e-mailadres van de medewerker mag maximaal 255 tekens bevatten',
            'email.unique' => 'Dit e-mailadres is al in gebruik',
            'role.required' => 'Kies een functie voor de medewerker',
            'role.in' => 'Kies een geldige functie voor de medewerker',
            'role.max' => 'De functie van de medewerker mag maximaal 50 tekens bevatten',
            'phone.digits' => 'Het telefoonnummer moet uit 10 cijfers bestaan',
        ];
    }
}
